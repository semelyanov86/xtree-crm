<?php

namespace Cron;

/**
 * Minutes field.  Allows: * , / -.
 */
class MinutesField extends AbstractField
{
    public function isSatisfiedBy(\DateTimeImmutable $date, $value)
    {
        return $this->isSatisfied($date->format('i'), $value);
    }

    public function increment(\DateTimeImmutable $date, $invert = false)
    {
        if ($invert) {
            $date->modify('-1 minute');
        } else {
            $date->modify('+1 minute');
        }

        return $this;
    }

    public function validate($value)
    {
        return (bool) preg_match('/[\*,\/\-0-9]+/', $value);
    }
}
