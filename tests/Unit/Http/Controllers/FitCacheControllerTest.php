<?php

	namespace Tests\Http\Controllers;

	use App\Http\Controllers\FitCacheController;
    use Tests\TestCase;

    class FitCacheControllerTest extends TestCase {

        public function testGetFitHash() {

            /** @var FitCacheController $controller */
            $controller = resolve("App\Http\Controllers\FitCacheController");

            $fit1_a = "[Tristan, Tristan fit]

Drone Damage Amplifier II
Drone Damage Amplifier II
Nanofiber Internal Structure II

Medium Shield Extender II
5MN Y-T8 Compact Microwarpdrive
Warp Disruptor II

125mm Gatling AutoCannon II, Republic Fleet Phased Plasma S
[Empty High slot]
[Empty High slot]

Small Anti-EM Screen Reinforcer I
Small Anti-Thermal Screen Reinforcer I
Small Anti-EM Screen Reinforcer I


Acolyte II x4
Warrior II x4";
            $fit1_b = "[Tristan, Tristan Best fit]

Drone Damage Amplifier II
Drone Damage Amplifier II
Nanofiber Internal Structure II

Medium Shield Extender II
5MN Y-T8 Compact Microwarpdrive
Warp Disruptor II

125mm Gatling AutoCannon II, Republic Fleet Phased Plasma S
[Empty High slot]
[Empty High slot]

Small Anti-EM Screen Reinforcer I
Small Anti-Thermal Screen Reinforcer I
Small Anti-EM Screen Reinforcer I


Acolyte II x4
Warrior II x4";
            $fit2_a = "[Tristan, bleh 1]

[Empty Low slot]
[Empty Low slot]
[Empty Low slot]

[Empty Med slot]
[Empty Med slot]
[Empty Med slot]

Small Inductive Compact Remote Capacitor Transmitter
[Empty High slot]
[Empty High slot]

[Empty Rig slot]
[Empty Rig slot]
[Empty Rig slot]";
            $fit2_b = "[Tristan, bleh 2]

[Empty Low slot]
[Empty Low slot]
[Empty Low slot]

[Empty Med slot]
[Empty Med slot]
[Empty Med slot]

Small Inductive Compact Remote Capacitor Transmitter
[Empty High slot]
[Empty High slot]

[Empty Rig slot]
[Empty Rig slot]
[Empty Rig slot]";

            $hash1_a = $controller->getFitHash($fit1_a);
            $hash1_b = $controller->getFitHash($fit1_b);
            $hash2_a = $controller->getFitHash($fit2_a);
            $hash2_b = $controller->getFitHash($fit2_b);

            $this->assertEquals($hash1_a, $hash1_b);
            $this->assertEquals($hash2_a, $hash2_b);
            $this->assertNotEquals($hash1_a, $hash2_a);
            $this->assertNotEquals($hash1_a, $hash2_b);
            $this->assertNotEquals($hash2_a, $hash1_a);
            $this->assertNotEquals($hash2_a, $hash1_b);



        }
    }
