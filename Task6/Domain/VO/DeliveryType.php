<?php

namespace Task6\Domain\VO;

enum DeliveryType: string
{
    case Courier = "Courier";
    case Pickup = "Pickup";
    case Post = 'Post';
}
