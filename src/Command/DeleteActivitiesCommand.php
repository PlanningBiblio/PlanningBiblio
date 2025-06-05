<?php

namespace App\Command;

use App\Model\Activity;
use App\Model\Agent;
use App\Model\Position;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-activities',
    description: 'Displays and deletes activities'
)]
class DeleteActivitiesCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        if ($id) {
            $io->note(sprintf('Activity %s will be deleted', $id));
        }

        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();
       
        $activities = $em->getRepository(Activity::class)->findBy(
            [],
            ['id' => 'ASC', 'nom' => 'ASC'],
        );

        $options = [];
        $options[0] = 'None (0)';
        foreach ($activities as $activity) {
          $options[$activity->getId()] = $activity->getName() . ' (' . $activity->getId() . ')';
        }

        $helper = $this->getHelper('question');
        $question1 = new ChoiceQuestion(
            'Please select activities to delete',
            $options
        );

        $question1->setMultiselect(true);
        $choices = $question1->getChoices();

        $answers = $helper->ask($input, $output, $question1);

        if ($answers[0] == 'None (0)') {
            $io->success('Operation canceled.');
            return Command::SUCCESS;
        }

        $answerList = '- ' . implode("\n- ", $answers);
    
        $question2 = new ConfirmationQuestion("The following activities will be deleted.\n$answerList\nContinue with this action [y/n]?", false, '/^(y)/i');

        if (!$helper->ask($input, $output, $question2)) {
            $io->success('Operation canceled.');
            return Command::SUCCESS;
        }

        $skillsToDelete = [];
        foreach($answers as $answer) {
            $skillsToDelete[] = (int) preg_replace('/.*\((\d*)\)$/', '$1', $answer);
        }

        $agents = $em->getRepository(Agent::class)->findAll();
        $positions = $em->getRepository(Position::class)->findAll();

        foreach ($skillsToDelete as $skill) {
            foreach ($agents as $a) {
                $agentId = $a->id();
                $agentSkills = $a->skills();

                if (($key = array_search($skill, $agentSkills)) !== false) {
                    unset($agentSkills[$key]);
                    $agent = $em->getRepository(Agent::class)->find($agentId);
                    $agent->setSkills($agentSkills);
                    $em->persist($agent);
                    $em->flush();
                }
            }

            foreach ($positions as $p) {
                $positionId = $p->id();
                $positionSkills = $p->getSkills();

                if (($key = array_search($skill, $positionSkills)) !== false) {
                    unset($positionSkills[$key]);

                    $position = $em->getRepository(Position::class)->find($positionId);
                    $position->setSkills($positionSkills);
                    $em->persist($position);
                    $em->flush();
                }
            }

            $activity = $em->getRepository(Activity::class)->find($skill);
            if ($activity) {
                $em->remove($activity);
                $em->flush();
            }
        }

        $io->success('The selected activities have been deleted.');
        return Command::SUCCESS;
    }
}
