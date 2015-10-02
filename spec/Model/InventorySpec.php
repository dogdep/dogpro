<?php

namespace spec\App\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InventorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('App\Model\Inventory');
    }

    function it_should_return_param_array()
    {
        $this->params->shouldBe([]);
    }

    function it_should_return_param_array_if_set()
    {
        $this->params = "test";
        $this->params->shouldBe(["test"]);
    }
}
