<?php

use App\Entity\Agent;
use App\Entity\Manager;
use App\Entity\Cron;
use App\Entity\Config;
use App\Entity\OverTime;
use App\Entity\Holiday;
use App\Entity\WorkingHour;
use Doctrine\ORM\EntityManagerInterface;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class HolidayControllerEditTest extends PLBWebTestCase
{

    public static function setUpBeforeClass(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Manager::class);
        $builder->delete(Holiday::class);
        $builder->delete(WorkingHour::class);
        $builder->delete(Overtime::class);

        $cron = $entityManager->getRepository(Cron::class)->findAll();
        foreach ($cron as $c){
            $c->setDisabled(true);
            $entityManager->persist($c);
        }

        $entityManager->flush();

        $hours = [
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
        ];

        $abreton = new Agent();
        $abreton->setLogin('abreton');
        $abreton->setLastName('Breton');
        $abreton->setFirstName('Aubert');
        $abreton->setSites(['1']);
        $abreton->setACL([99,100]);
        $abreton->setHolidayAnnualCredit(120);
        $abreton->setHolidayCredit(24);
        $abreton->setHolidayRemainder(3);

        $entityManager->persist($abreton);
        $entityManager->flush();

        $workingHours = new WorkingHour();
        $workingHours->setUser($abreton->getId());
        $workingHours->setStart(new DateTime('2026-01-01'));
        $workingHours->setEnd(new DateTime('2026-12-31'));
        $workingHours->setWorkingHours($hours);
        $workingHours->setBreaktime([1,1,1,1,1,1]);
        $workingHours->setValidLevel2(1);

        $entityManager->persist($workingHours);
        $entityManager->flush();

        $jdevoe = new Agent();
        $jdevoe->setLogin('jdevoe');
        $jdevoe->setLastName('Devoe');
        $jdevoe->setFirstName('John');
        $jdevoe->setSites(['1']);
        $jdevoe->setACL([99,100]);
        $jdevoe->setHolidayCompTime(14.5);

        $entityManager->persist($jdevoe);
        $entityManager->flush();

        $hours2 = [
            ['10:00:00', '', '', '15:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['10:00:00', '', '', '15:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '18:00:00'],
            ['10:00:00', '', '', '19:00:00'],
        ];

        $workingHours2 = new WorkingHour();
        $workingHours2->setUser($jdevoe->getId());
        $workingHours2->setStart(new DateTime('2026-01-01'));
        $workingHours2->setEnd(new DateTime('2026-12-31'));
        $workingHours2->setWorkingHours($hours2);
        $workingHours2->setBreaktime([1,1,1,1,1,1]);
        $workingHours2->setValidLevel2(1);

        $entityManager->persist($workingHours2);
        $entityManager->flush();

        $jdupont = new Agent();
        $jdupont->setLogin('jdupont');
        $jdupont->setLastName('Dupont');
        $jdupont->setFirstName('Jean');
        $jdupont->setSites(['1', '2']);
        $jdupont->setACL([3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301]);

        $entityManager->persist($jdupont);
        $entityManager->flush();

        $start = new \DateTime('2026-07-15 00:00:00');
        $end = new \DateTime('2026-07-17 23:59:59');

        $holiday = new Holiday();
        $holiday->setUser($abreton->getId());
        $holiday->setStart($start);
        $holiday->setEnd($end);
        $holiday->setDebit('credit');
        $holiday->setHours('0.0');
        $holiday->setEntryDate($start);
        $holiday->setEntry($jdupont->getId());

        $entityManager->persist($holiday);

        $start = new \DateTime('2026-07-15 00:00:00');
        $end = new \DateTime('2026-07-15 23:59:59');

        $comptime = new Holiday();
        $comptime->setUser($jdevoe->getId());
        $comptime->setStart($start);
        $comptime->setEnd($end);
        $comptime->setDebit('recuperation');
        $comptime->setHours('5.0');
        $comptime->setEntryDate($start);
        $comptime->setEntry($jdupont->getId());

        $entityManager->persist($comptime);
        $entityManager->flush();

        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Enable'])->setValue(1);
        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Mode'])->setValue('heures');
        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Recuperations'])->setValue(1);
        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Validation'])->setValue(1);
        $entityManager->flush();
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Manager::class);
        $builder->delete(Holiday::class);
        $builder->delete(WorkingHour::class);
        $builder->delete(Overtime::class);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $cron = $entityManager->getRepository(Cron::class)->findAll();
        foreach ($cron as $c){
            $c->setDisabled(false);
            $entityManager->persist($c);
        }

        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Enable'])->setValue(0);
        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Mode'])->setValue('jours');
        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Recuperations'])->setValue(0);
        $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'Conges-Validation'])->setValue(0);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testHolidayEditWithoutRights(): void
    {
        $this->setUpPantherClient();

        $jdevoe = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdevoe']);
        $abreton = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'abreton']);
        $holiday = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $abreton->getId()]);

        $this->login($jdevoe);
        $crawler = $this->client->request('GET', '/holiday/edit/' . $holiday->getId());
        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());

        $this->assertSelectorExists('#acces_refuse');
    }

    public function testHolidayEditNotValidated(): void
    {
        $this->setUpPantherClient();

        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $abreton = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'abreton']);
        $holiday = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $abreton->getId()]);

        // Make jdupont manager of abreton
        $manager = new Manager();
        $manager->setUser($abreton);
        $manager->setLevel2(1);
        $jdupont->addManaged($manager);

        $this->login($jdupont);
        $crawler = $this->client->request('GET', '/holiday/edit/' . $holiday->getId());
        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());

        $this->assertSelectorTextContains('h3', 'Demande de congés');
        $this->assertSelectorExists('#holiday-form');

        // Verify form values
        $form = $crawler->filter('#holiday-form')->form();

        $agentLabel = $crawler->filter('label[for=agent]');
        $this->assertEquals('Agent :', $agentLabel->text(), 'Form agent label incorrect');
        $agent = $crawler->filter('input#agent.form-control-plaintext');
        $this->assertEquals('Breton Aubert', $agent->attr('value'), 'Form agent value incorrect');
        $this->assertNotNull($agent->attr('readonly'), 'The agent value should be readonly');

        $this->assertEquals('15/07/2026',  $form->getValues()['debut'],'The start-date value is incorrect');
        $this->assertEquals('17/07/2026',  $form->getValues()['fin'],'The end-date value is incorrect');

        $this->assertEquals('21',  $form->getValues()['heures'],'The number of hours of the holiday is incorrect');
        $this->assertEquals('00',  $form->getValues()['minutes'],'The number of minutes of the holiday is incorrect');

        $balance = $crawler->filter('input#holiday_balance');
        $this->assertStringContainsString('3h00',$balance->attr('value'),'The balance value is incorrect');
        $credit = $crawler->filter('input#holiday_credit');
        $this->assertStringContainsString('24h00',$credit->attr('value'),'The credit value is incorrect');
        $debit = $crawler->filter('input#holiday_debit');
        $this->assertStringContainsString('0h00',$debit->attr('value'),'The debit value is incorrect');

        $balance2 = $crawler->filter('span#reliquat4');
        $this->assertStringContainsString('0h00',$balance2->text(),'The balance after calculation value is incorrect');
        $credit2 = $crawler->filter('span#credit4');
        $this->assertStringContainsString('6h00',$credit2->text(),'The credit after calculation value is incorrect');
        $debit2 = $crawler->filter('span#anticipation4');
        $this->assertStringContainsString('0h00',$debit2->text(),'The debit after calculation value is incorrect');

        $this->assertStringContainsString('par Dupont J', $form->getValues()['request'], 'The request value is incorrect');
        $this->assertEquals(0,  $form->getValues()['valide'],'The validation state is incorrect');

        // Buttons
        $this->assertSelectorExists('input#cancel');
        $this->assertSelectorExists('input.btn-primary[type=submit]');
        $this->assertSelectorExists('input.btn-danger');

        $saveButton = $crawler->filter('input.btn-primary');
        $this->assertEquals('Enregistrer les modifications', $saveButton->attr('value'), 'The save button value is incorrect');

        $this->getSelect('validation-state')->selectByValue(1);

        // Validate form
        $this->client->submit($form);

    }

    public function testHolidayEditValidated(): void
    {
        $this->setUpPantherClient();
        $abreton = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'abreton']);
        $holiday = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $abreton->getId()]);

        $this->login($abreton);
        $crawler = $this->client->request('GET', '/holiday/edit/' . $holiday->getId());
        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());

        $this->assertSelectorNotExists('#holiday_balance');
        $this->assertSelectorNotExists('#holiday_credit');
        $this->assertSelectorNotExists('#holiday_debit');
        $this->assertSelectorIsNotVisible('#terms');

        $startDate = $crawler->filter('.start-date');
        $this->assertNotNull($startDate->attr('readonly'), 'The start-date datepicker object should be readonly');
        $endDate = $crawler->filter('.end-date');
        $this->assertNotNull($endDate->attr('readonly'), 'The end-date datepicker object should be readonly');

        // Buttons
        $this->assertSelectorExists('input#cancel');
        $this->assertSelectorNotExists('input.btn-primary[type=submit]');
        $this->assertSelectorNotExists('input.btn-danger');

    }

    public function testComptimeEditNotValidated(): void
    {

        $this->setUpPantherClient();
        $jdevoe = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdevoe']);
        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $comptime = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $jdevoe->getId()]);

        // Make jdupont manager of jdevoe
        $manager = new Manager();
        $manager->setUser($jdevoe);
        $manager->setLevel2(1);
        $jdupont->addManaged($manager);

        $this->login($jdupont);
        $crawler = $this->client->request('GET', '/holiday/edit/' . $comptime->getId());
        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());

        $this->assertSelectorTextContains('h3', 'Demande de récupérations');
        $this->assertSelectorExists('#holiday-form');

        // Verify form values
        $form = $crawler->filter('#holiday-form')->form();

        $agent = $crawler->filter('input#agent.form-control-plaintext');
        $this->assertEquals('Devoe John', $agent->attr('value'), 'Form agent value incorrect');
        $this->assertNotNull($agent->attr('readonly'), 'The agent value should be readonly');

        $this->assertEquals('15/07/2026',  $form->getValues()['debut'],'The start-date value is incorrect');

        $this->assertEquals('4',  $form->getValues()['heures'],'The number of hours of the holiday is incorrect');
        $this->assertEquals('00',  $form->getValues()['minutes'],'The number of minutes of the holiday is incorrect');

        $balance = $crawler->filter('input#balance_before');
        $this->assertStringContainsString('14h30', $balance->attr('value'),'The balance value is incorrect');
        $balance_after = $crawler->filter('span#recup4');
        $this->assertStringContainsString('10h30', $balance_after->text(),'The balance after calculation value is incorrect');
        
        $this->assertSelectorExists('#balance2_before');
        $this->assertSelectorExists('#balance2_after');

        $this->assertSelectorNotExists('#holiday_balance');
        $this->assertSelectorNotExists('#holiday_credit');
        $this->assertSelectorNotExists('#holiday_debit');

        $this->assertStringContainsString('par Dupont J', $form->getValues()['request'], 'The request value is incorrect');
        $this->assertEquals(0,  $form->getValues()['valide'],'The validation state is incorrect');

        // Buttons
        $this->assertSelectorExists('input.btn-secondary');
        $this->assertSelectorExists('input.btn-primary[type=submit]');
        $this->assertSelectorExists('input.btn-danger');

        $saveButton = $crawler->filter('input.btn-primary');
        $this->assertEquals('Enregistrer les modifications', $saveButton->attr('value'), 'The save button value is incorrect');

        $this->getSelect('validation-state')->selectByValue(1);

        // Validate form
        $this->client->submit($form);

    }

    public function testComptimeEditValidated(): void
    {
        $this->setUpPantherClient();
        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $jdevoe = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdevoe']);
        $comptime = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $jdevoe->getId()]);

        $this->login($jdupont);
        $crawler = $this->client->request('GET', '/holiday/edit/' . $comptime->getId());
        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());

        $this->assertSelectorNotExists('#balance_before');
        $this->assertSelectorNotExists('#balance2_before');
        $this->assertSelectorIsNotVisible('#terms');

        $startDate = $crawler->filter('.start-date');
        $this->assertNotNull($startDate->attr('readonly'), 'The start-date datepicker object should be readonly');
        $endDate = $crawler->filter('.end-date');
        $this->assertNotNull($endDate->attr('readonly'), 'The end-date datepicker object should be readonly');

        // Buttons
        $this->assertSelectorExists('input.btn-secondary');
        $this->assertSelectorNotExists('input.btn-primary[type=submit]');
        $this->assertSelectorExists('input.btn-danger');

    }

}
