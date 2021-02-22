<?php

namespace WillemStuursma\CastBlock\ValueObjects;

final class ChromeCast
{
    private $device;

    private $deviceName;

    private $address;

    private $uuid;

    public function __construct(string $device, string $deviceName, string $address, string $uuid)
    {
        $this->device = $device;
        $this->deviceName = $deviceName;
        $this->address = $address;
        $this->uuid = $uuid;
    }

    /**
     * @return self[]
     */
    public static function fromGoChromeCastOutput(string $output): array
    {
        $return = [];

        $lines = explode(PHP_EOL, $output);

        foreach ($lines as $line) {
            $matched = preg_match(
                "!
                device=\"(?P<device>[^\"]+)\"\\s*
                device_name=\"(?P<deviceName>[^\"]+)\"\\s*
                address=\"(?P<address>[^\"]+)\"\\s*
                uuid=\"(?P<uuid>[^\"]+)\"\\s*
            !x",
                $line,
                $matches
            );

            if (!$matched) {
                /*
                 * Line did not match regular expression, either empty or different type of output.
                 */
                continue;
            }

            [
                "device" => $device,
                "deviceName" => $deviceName,
                "address" => $address,
                "uuid" => $uuid,
            ] = $matches;


            $return[] = new self($device, $deviceName, $address, $uuid);
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getDevice(): string
    {
        return $this->device;
    }

    /**
     * @return string
     */
    public function getDeviceName(): string
    {
        return $this->deviceName;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    public function getAddressWithoutPort(): string
    {
        return explode(":", $this->getAddress())[0];
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function __toString(): string
    {
        return "{$this->getDevice()} {$this->getDeviceName()}";
    }
}