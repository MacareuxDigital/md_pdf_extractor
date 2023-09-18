<?php

namespace Macareux\Package\PdfExtractor\File\Import\Processor;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\File\Import\ImportingFile;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Import\Processor\PostProcessorInterface;
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
        $pdf = $parser->parseContent($importedVersion->getFileContents());
        $importedVersion->setAttribute(self::AKHANDLE, $pdf->getText());
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
