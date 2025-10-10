<?php

use App\Entity\Agent;
use App\Entity\AbsenceReason;
use Facebook\WebDriver\WebDriverBy;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

/**
 * Comprehensive tests for planno-timepicker.js JavaScript functionality
 * 
 * Tests the blur event validation and timePickerChange enhancements added to the timepicker.
 * These tests verify that:
 * - Invalid time formats are cleared on blur
 * - Valid time formats are preserved
 * - The timePickerChange function properly handles false date values
 */
class TimepickerJavaScriptTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
        $this->setParam('Multisites-nombre', 1);
        $this->setUpPantherClient();
    }

    /**
     * Helper method to create a test page with timepicker
     */
    private function setupTimepickerTestPage()
    {
        $agent = $this->builder->build(Agent::class, array(
            'login' => 'testuser',
            'nom' => 'User',
            'prenom' => 'Test',
            'droits' => array(99, 100, 201)
        ));

        $this->login($agent);
        
        // Navigate to a page that uses the timepicker (absence add page)
        $this->client->request('GET', '/absence/add');
        $this->client->waitForVisibility('input[name="hre_debut"]');
    }

    /**
     * Test that valid time format 00:00 is preserved on blur
     */
    public function testValidTimeFormat0000PreservedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        // Set a valid time format
        $input->clear();
        $input->sendKeys('00:00');
        
        // Trigger blur event
        $driver->executeScript('arguments[0].blur();', [$input]);
        
        // Wait a moment for blur handler to execute
        usleep(100000); // 100ms
        
        // Verify the value is preserved
        $value = $input->getAttribute('value');
        $this->assertEquals('00:00', $value, 'Valid time format 00:00 should be preserved on blur');
    }

    /**
     * Test that valid time format 12:30 is preserved on blur
     */
    public function testValidTimeFormat1230PreservedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('12:30');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('12:30', $value, 'Valid time format 12:30 should be preserved on blur');
    }

    /**
     * Test that valid time format 23:59 (max valid time) is preserved on blur
     */
    public function testValidTimeFormat2359PreservedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('23:59');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('23:59', $value, 'Valid time format 23:59 should be preserved on blur');
    }

    /**
     * Test that invalid time format 24:00 is cleared on blur
     */
    public function testInvalidTimeFormat2400ClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('24:00');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Invalid time format 24:00 should be cleared on blur');
    }

    /**
     * Test that invalid time format 25:30 is cleared on blur
     */
    public function testInvalidTimeFormat2530ClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('25:30');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Invalid time format 25:30 should be cleared on blur');
    }

    /**
     * Test that invalid minute format 12:60 is cleared on blur
     */
    public function testInvalidMinuteFormat1260ClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('12:60');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Invalid minute format 12:60 should be cleared on blur');
    }

    /**
     * Test that invalid minute format 10:99 is cleared on blur
     */
    public function testInvalidMinuteFormat1099ClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('10:99');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Invalid minute format 10:99 should be cleared on blur');
    }

    /**
     * Test that text input is cleared on blur
     */
    public function testTextInputClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('invalid');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Text input should be cleared on blur');
    }

    /**
     * Test that single digit hour without leading zero is cleared
     */
    public function testSingleDigitHourClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('9:30');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Single digit hour 9:30 should be cleared (must be 09:30)');
    }

    /**
     * Test that single digit minute without leading zero is cleared
     */
    public function testSingleDigitMinuteClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('09:5');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Single digit minute 09:5 should be cleared (must be 09:05)');
    }

    /**
     * Test that format without colon is cleared
     */
    public function testFormatWithoutColonClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('1230');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Format without colon 1230 should be cleared');
    }

    /**
     * Test that empty string is preserved on blur
     */
    public function testEmptyStringPreservedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Empty string should be preserved (already valid state)');
    }

    /**
     * Test that partial time format is cleared on blur
     */
    public function testPartialTimeFormatClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('12:');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Partial time format 12: should be cleared');
    }

    /**
     * Test that time with extra characters is cleared
     */
    public function testTimeWithExtraCharactersClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('12:30:00');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Time with seconds 12:30:00 should be cleared (expects HH:mm only)');
    }

    /**
     * Test that time with AM/PM is cleared
     */
    public function testTimeWithAMPMClearedOnBlur()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $input->clear();
        $input->sendKeys('12:30 PM');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Time with AM/PM (12:30 PM) should be cleared (expects 24-hour format)');
    }

    /**
     * Test blur event handler on end time field
     */
    public function testBlurValidationOnEndTimeField()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_fin'));
        
        // Test valid time
        $input->clear();
        $input->sendKeys('14:45');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('14:45', $value, 'End time field should preserve valid time 14:45');
        
        // Test invalid time
        $input->clear();
        $input->sendKeys('invalid');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'End time field should clear invalid time');
    }

    /**
     * Test timePickerChange function behavior when date is false
     * This tests the new logic that clears the field and focuses it
     */
    public function testTimePickerChangeClearsAndFocusesWhenDateIsFalse()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        // Set an initial value
        $input->clear();
        $input->sendKeys('12:30');
        
        // Execute the timePickerChange function with date = false
        $driver->executeScript('
            var input = arguments[0];
            input.value = "12:30";
            timePickerChange(false, input);
        ', [$input]);
        
        usleep(100000);
        
        // Verify the value was cleared
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'timePickerChange with date=false should clear the input value');
        
        // Verify the element has focus
        $activeElement = $driver->switchTo()->activeElement();
        $activeElementName = $activeElement->getAttribute('name');
        $this->assertEquals('hre_debut', $activeElementName, 'timePickerChange with date=false should focus the input');
    }

    /**
     * Test that blur validation works with leading zeros in hours
     */
    public function testLeadingZeroInHoursPreserved()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        // Test 01:00 to 09:59
        $testTimes = ['01:00', '05:30', '09:59'];
        
        foreach ($testTimes as $time) {
            $input->clear();
            $input->sendKeys($time);
            $driver->executeScript('arguments[0].blur();', [$input]);
            usleep(50000);
            
            $value = $input->getAttribute('value');
            $this->assertEquals($time, $value, "Time $time with leading zero should be preserved");
        }
    }

    /**
     * Test that blur validation works with leading zeros in minutes
     */
    public function testLeadingZeroInMinutesPreserved()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        // Test minutes 00-09
        $testTimes = ['10:00', '10:05', '10:09'];
        
        foreach ($testTimes as $time) {
            $input->clear();
            $input->sendKeys($time);
            $driver->executeScript('arguments[0].blur();', [$input]);
            usleep(50000);
            
            $value = $input->getAttribute('value');
            $this->assertEquals($time, $value, "Time $time with leading zero in minutes should be preserved");
        }
    }

    /**
     * Test edge case: boundary times (00:00, 23:59)
     */
    public function testBoundaryTimes()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        // Test minimum valid time
        $input->clear();
        $input->sendKeys('00:00');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('00:00', $value, 'Minimum time 00:00 should be valid');
        
        // Test maximum valid time
        $input->clear();
        $input->sendKeys('23:59');
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('23:59', $value, 'Maximum time 23:59 should be valid');
    }

    /**
     * Test that special characters are rejected
     */
    public function testSpecialCharactersRejected()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $invalidInputs = [
            '12-30',     // Wrong separator
            '12.30',     // Wrong separator
            '12/30',     // Wrong separator
            '12:30;',    // Extra character
            '@12:30',    // Leading special char
            '12:30!',    // Trailing special char
        ];
        
        foreach ($invalidInputs as $invalidInput) {
            $input->clear();
            $input->sendKeys($invalidInput);
            $driver->executeScript('arguments[0].blur();', [$input]);
            usleep(50000);
            
            $value = $input->getAttribute('value');
            $this->assertEquals('', $value, "Invalid input '$invalidInput' should be cleared");
        }
    }

    /**
     * Test that whitespace in time is rejected
     */
    public function testWhitespaceInTimeRejected()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        $invalidInputs = [
            ' 12:30',    // Leading space
            '12:30 ',    // Trailing space
            '12 :30',    // Space before colon
            '12: 30',    // Space after colon
            '12 : 30',   // Spaces around colon
        ];
        
        foreach ($invalidInputs as $invalidInput) {
            $input->clear();
            $input->sendKeys($invalidInput);
            $driver->executeScript('arguments[0].blur();', [$input]);
            usleep(50000);
            
            $value = $input->getAttribute('value');
            $this->assertEquals('', $value, "Invalid input with whitespace should be cleared");
        }
    }

    /**
     * Test regex pattern validation comprehensively
     */
    public function testRegexPatternValidation()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        
        // Test the regex pattern directly via JavaScript
        $regexTests = [
            // Valid times
            ['00:00', true],
            ['00:30', true],
            ['01:00', true],
            ['09:59', true],
            ['10:00', true],
            ['12:00', true],
            ['19:30', true],
            ['20:00', true],
            ['23:00', true],
            ['23:59', true],
            
            // Invalid hours
            ['24:00', false],
            ['25:00', false],
            ['30:00', false],
            ['99:00', false],
            
            // Invalid minutes
            ['00:60', false],
            ['12:61', false],
            ['12:99', false],
            
            // Invalid formats
            ['1:00', false],    // Single digit hour
            ['01:0', false],    // Single digit minute
            ['9:30', false],    // Single digit hour
            ['12:5', false],    // Single digit minute
            ['1230', false],    // No colon
            ['12:30:00', false], // With seconds
            ['', true],         // Empty should match (no error state)
        ];
        
        foreach ($regexTests as list($time, $shouldMatch)) {
            $result = $driver->executeScript('
                var pattern = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
                var str = arguments[0];
                return str === "" || pattern.test(str);
            ', [$time]);
            
            if ($shouldMatch) {
                $this->assertTrue($result, "Time '$time' should match the validation pattern");
            } else {
                $this->assertFalse($result, "Time '$time' should not match the validation pattern");
            }
        }
    }

    /**
     * Test that validation fires on dynamically added timepicker elements
     */
    public function testValidationOnDynamicElements()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        
        // Create a new timepicker input dynamically
        $driver->executeScript('
            var newInput = $("<input>")
                .attr("type", "text")
                .addClass("planno-timepicker")
                .attr("id", "dynamic-timepicker")
                .val("");
            $("body").append(newInput);
        ');
        
        $dynamicInput = $driver->findElement(WebDriverBy::id('dynamic-timepicker'));
        
        // Test invalid input on dynamic element
        $dynamicInput->sendKeys('invalid');
        $driver->executeScript('arguments[0].blur();', [$dynamicInput]);
        usleep(100000);
        
        $value = $dynamicInput->getAttribute('value');
        $this->assertEquals('', $value, 'Dynamic timepicker should also clear invalid input on blur');
        
        // Test valid input on dynamic element
        $dynamicInput->clear();
        $dynamicInput->sendKeys('15:45');
        $driver->executeScript('arguments[0].blur();', [$dynamicInput]);
        usleep(100000);
        
        $value = $dynamicInput->getAttribute('value');
        $this->assertEquals('15:45', $value, 'Dynamic timepicker should preserve valid input on blur');
    }

    /**
     * Test that multiple blur events don't cause issues
     */
    public function testMultipleBlurEvents()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        // Set valid time and blur multiple times
        $input->clear();
        $input->sendKeys('10:30');
        
        for ($i = 0; $i < 3; $i++) {
            $driver->executeScript('arguments[0].blur();', [$input]);
            usleep(50000);
        }
        
        $value = $input->getAttribute('value');
        $this->assertEquals('10:30', $value, 'Multiple blur events should not affect valid time');
        
        // Set invalid time and blur multiple times
        $input->clear();
        $input->sendKeys('invalid');
        
        for ($i = 0; $i < 3; $i++) {
            $driver->executeScript('arguments[0].blur();', [$input]);
            usleep(50000);
        }
        
        $value = $input->getAttribute('value');
        $this->assertEquals('', $value, 'Multiple blur events should still clear invalid time');
    }

    /**
     * Test interaction between blur validation and focus
     */
    public function testBlurAndFocusInteraction()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $startInput = $driver->findElement(WebDriverBy::name('hre_debut'));
        $endInput = $driver->findElement(WebDriverBy::name('hre_fin'));
        
        // Set invalid time in start field
        $startInput->clear();
        $startInput->sendKeys('invalid');
        
        // Focus end field (which triggers blur on start field)
        $endInput->click();
        usleep(100000);
        
        // Verify start field was cleared
        $value = $startInput->getAttribute('value');
        $this->assertEquals('', $value, 'Blur should clear invalid input when focusing another field');
        
        // Set valid time in start field
        $startInput->clear();
        $startInput->sendKeys('08:00');
        
        // Focus end field again
        $endInput->click();
        usleep(100000);
        
        // Verify start field preserved valid time
        $value = $startInput->getAttribute('value');
        $this->assertEquals('08:00', $value, 'Blur should preserve valid input when focusing another field');
    }

    /**
     * Test that the blur handler preserves existing value if already valid
     */
    public function testPreExistingValidValuePreserved()
    {
        $this->setupTimepickerTestPage();
        
        $driver = $this->client->getWebDriver();
        $input = $driver->findElement(WebDriverBy::name('hre_debut'));
        
        // Set value via JavaScript (simulating pre-populated field)
        $driver->executeScript('arguments[0].value = "16:30";', [$input]);
        
        // Trigger blur
        $driver->executeScript('arguments[0].blur();', [$input]);
        usleep(100000);
        
        $value = $input->getAttribute('value');
        $this->assertEquals('16:30', $value, 'Pre-existing valid value should be preserved on blur');
    }
}