<?php

namespace Macareux\Package\PdfExtractor\File\Import\Processor;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\File\Import\ImportingFile;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Import\Processor\PostProcessorInterface;
use Concrete\Core\Support\Facade\Application;
use Psr\Log\LoggerInterface;
use Smalot\PdfParser\Parser;

class PdfExtractor implements PostProcessorInterface
{
    public const AKHANDLE = 'pdf_text';

    protected $enabled = true;

    /**
     * {@inheritdoc}
     */
    public function getPostProcessPriority()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldPostProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion)
    {
        return $this->enabled && $file->getFileType()->getName() === 'PDF';
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion)
    {
        $parser = new Parser();
        try {
            $pdf = $parser->parseContent($importedVersion->getFileContents());
            $importedVersion->setAttribute(self::AKHANDLE, $pdf->getText());
        } catch (\Exception $e) {
            $app = Application::getFacadeApplication();
            /** @var LoggerInterface $logger */
            $logger = $app->make('log/application');
            $logger->error(sprintf('Failed to extract test from PDF file %s. Error message: %s', $importedVersion->getFileID(), $e->getMessage()), ['exception' => $e]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readConfiguration(Repository $config)
    {
        $this->enabled = $config->get('md_pdf_extractor::file_manager.pdf_extractor', true);

        return $this;
    }
}
