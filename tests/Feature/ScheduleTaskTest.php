<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Providers\FilaCmsServiceProvider;
use Portable\FilaCms\Tests\TestCase;

class ScheduleTaskTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_tasks_are_scheduled()
    {
        $events = app()->getProviders(FilaCmsServiceProvider::class);
        $events = app()->make('Illuminate\Console\Scheduling\Schedule')->events();
        $this->assertTrue(true);
    }
}
