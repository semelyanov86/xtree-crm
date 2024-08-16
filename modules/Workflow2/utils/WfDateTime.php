<?php

class WfDateTime extends DateTimeImmutable
{
    public function getTimestamp()
    {
        return method_exists('DateTime', 'getTimestamp') ?
            parent::getTimestamp() : $this->format('U');
    }
}
