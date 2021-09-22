<?php

namespace DCKAP\Version\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DDIVersion extends Command
{
    const MODULE_NAME="DCKAP_Version";
    private $_moduleList;

    /**
     * DDIVersion constructor.
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param string|null $name
     */
    public function __construct(
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        string $name = null
    ) {
        $this->_moduleList=$moduleList;
        parent::__construct($name);
    }

    /**
     *  Setting command name and description
     */
    protected function configure()
    {
        $this->setName('ddi:version');
        $this->setDescription('DDI Template Version');
        parent::configure();
    }

    /**
     * Return the version from the setup_version
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $version='v'.$this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
        $output->writeln('DDI Version - '.$version);
    }
}
