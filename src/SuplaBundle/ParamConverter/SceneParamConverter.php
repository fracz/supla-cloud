<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;

class SceneParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /**
     * @var LocationRepository
     */
    private $locationRepository;

    public function __construct(
        IODeviceChannelRepository $channelRepository,
        ChannelGroupRepository $channelGroupRepository,
        ChannelActionExecutor $channelActionExecutor,
        LocationRepository $locationRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->channelGroupRepository = $channelGroupRepository;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->locationRepository = $locationRepository;
    }

    public function getConvertedClass(): string {
        return Scene::class;
    }

    public function convert(array $data) {
        $user = $this->getCurrentUserOrThrow();
        if (isset($data['locationId']) && $data['locationId']) {
            $location = $this->locationRepository->findForUser($user, $data['locationId']);
        } else {
            $location = $user->getLocations()[0];
        }
        $scene = new Scene($location);
        $scene->setCaption($data['caption'] ?? '');
        $scene->setEnabled($data['enabled'] ?? false);
        $operations = $data['operations'] ?? [];
        Assertion::isArray($operations, 'Invalid operations spec.');
        Assertion::allIsArray($operations, 'Invalid operations spec.');
//        Assertion::greaterThan(count($channelIds), 0, 'Channel group must consist of at least one channel.'); // i18n
        $operations = array_map(function (array $operationData) use ($scene, $user) {
            Assertion::keyExists($operationData, 'subjectId', 'You must set subjectId for each scene operation.');
            Assertion::keyExists($operationData, 'subjectType', 'You must set subjectType for each scene operation.');
            Assertion::keyExists($operationData, 'actionId', 'You must set action for each scene operation.');
            Assertion::inArray($operationData['subjectType'], ActionableSubjectType::toArray(), 'Invalid subject type.');
            /** @var HasFunction $subject */
            $subject = $operationData['subjectType'] === ActionableSubjectType::CHANNEL
                ? $this->channelRepository->findForUser($user, $operationData['subjectId'])
                : $this->channelGroupRepository->findForUser($user, $operationData['subjectId']);
            $action = ChannelFunctionAction::fromString($operationData['actionId']);
            $actionParam = $operationData['actionParam'] ?? [] ?: [];
            $actionParam = $this->channelActionExecutor->validateActionParams($subject, $action, $actionParam);
            Assertion::inArray($action->getId(), EntityUtils::mapToIds($subject->getFunction()->getPossibleActions()));
            $delayMs = $operationData['delayMs'] ?? 0;
            return new SceneOperation($subject, $action, $actionParam, $delayMs);
        }, $operations);
        $scene->setOpeartions($operations);
        return $scene;
    }
}