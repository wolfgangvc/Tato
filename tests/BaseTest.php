<?php

namespace Tato\Test;

use Faker\Provider;
use Faker\Factory as FakerFactory;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Faker\Generator */
    protected $faker;
    
    public function setUp()
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->faker->addProvider(new Provider\Base($this->faker));
        $this->faker->addProvider(new Provider\DateTime($this->faker));
        $this->faker->addProvider(new Provider\Lorem($this->faker));
        $this->faker->addProvider(new Provider\Internet($this->faker));
        $this->faker->addProvider(new Provider\Payment($this->faker));
        $this->faker->addProvider(new Provider\en_US\Person($this->faker));
        $this->faker->addProvider(new Provider\en_US\Address($this->faker));
        $this->faker->addProvider(new Provider\en_US\PhoneNumber($this->faker));
        $this->faker->addProvider(new Provider\en_US\Company($this->faker));
    }
}
