<?php

namespace App\Command;

use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../public/include/db.php');

#[AsCommand(
    name: 'app:holiday:reset-credits',
    description: 'Reset the credits for holiday',
)]
class HolidayResetCreditsCommand extends Command
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->entityManager->getRepository(Config::class)->getAll();
        $transferCompTime = $config['Conges-transfer-comp-time'];

        $CSRFToken = CSRFToken();

        $p=new \personnel();
        $p->supprime=array(0,1);
        $p->fetch();
        if ($p->elements) {
            foreach ($p->elements as $elem) {
                $credits=array();
                $credits['conges_credit'] = floatval($elem['conges_annuel']) - floatval($elem['conges_anticipation']);
                $credits['conges_anticipation'] = 0;

                if (!empty($transferCompTime)) {
                    $credits['conges_reliquat'] = floatval($elem['conges_credit']) + floatval($elem['comp_time']);
                    $credits['comp_time'] = 0;
                } else {
                    $credits['conges_reliquat'] = $elem['conges_credit'];
                    $credits['comp_time'] = $elem['comp_time'];
                }

                $c=new \conges();
                $c->perso_id=$elem['id'];
                $c->CSRFToken = $CSRFToken;
                $c->maj($credits, "modif", true);
            }
        }

        // Modifie les crédits
        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        if (!empty($transferCompTime)) {
            $db->update("personnel", "conges_reliquat=(conges_credit+comp_time)");
        } else {
            $db->update("personnel", "conges_reliquat=conges_credit");
        }
        if (!empty($transferCompTime)) {
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update("personnel", "comp_time='0.00'");
        }
        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update("personnel", "conges_credit=(conges_annuel-conges_anticipation)");
        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update("personnel", "conges_anticipation=0.00");


        if ($output->isVerbose())
        {
            $io->success('Reset the credits for holiday successfully!');
        }

        return Command::SUCCESS;
    }
}
