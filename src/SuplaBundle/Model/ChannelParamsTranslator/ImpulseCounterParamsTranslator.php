<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Utils\NumberUtils;

class ImpulseCounterParamsTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 10000, 4),
            'impulsesPerUnit' => $channel->getParam3(),
            'currency' => $channel->getTextParam1(),
            'customUnit' => $channel->getTextParam2(),
            'initialValue' => $channel->getParam1(),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['initialValue'])) {
            $channel->setParam1($this->getValueInRange($config['initialValue'], 0, 1000000));
        }
        if (isset($config['pricePerUnit'])) {
            $channel->setParam2($this->getValueInRange($config['pricePerUnit'], 0, 1000) * 10000);
        }
        if (isset($config['impulsesPerUnit'])) {
            $channel->setParam3($this->getValueInRange($config['impulsesPerUnit'], 0, 1000000));
        }
        if (isset($config['currency'])) {
            $currency = $config['currency'];
            if (!$currency || preg_match('/^[A-Z]{3}$/', $currency)) {
                $channel->setTextParam1($currency);
            }
        }
        if (isset($config['customUnit'])) {
            if (strlen($config['customUnit']) <= 4) {
                $channel->setTextParam2($config['customUnit']);
            }
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getType()->getId() == ChannelType::IMPULSECOUNTER &&
            in_array($channel->getFunction()->getId(), [
                ChannelFunction::ELECTRICITYMETER,
                ChannelFunction::GASMETER,
                ChannelFunction::WATERMETER,
            ]);
    }
}