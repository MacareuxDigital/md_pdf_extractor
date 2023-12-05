<?php

namespace Concrete\Package\MdPdfExtractor;

use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Attribute\Category\FileCategory;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\Attribute\Key\FileKey;
use Concrete\Core\Package\Package;
use Macareux\Package\PdfExtractor\File\Import\Processor\PdfExtractor;

class Controller extends Package
{
    protected $appVersionRequired = '9.0.0';

    protected $pkgHandle = 'md_pdf_extractor';

    protected $pkgVersion = '0.9.1';

    protected $pkgAutoloaderRegistries = [
        'src' => '\Macareux\Package\PdfExtractor',
    ];

    public function getPackageName()
    {
        return t('Macareux PDF Extractor');
    }

    public function getPackageDescription()
    {
        return t('Import text from PDF file on uploading it to file manager.');
    }

    public function install()
    {
        if (!file_exists($this->getPackagePath() . '/vendor/autoload.php')) {
            throw new \RuntimeException('Required libraries not found.');
        }

        $pkg = parent::install();
        /** @var CategoryService $service */
        $service = $this->app->make(CategoryService::class);
        $categoryEntity = $service->getByHandle('file');
        /** @var FileCategory $category */
        $category = $categoryEntity->getController();
        $ak = $category->getAttributeKeyByHandle(PdfExtractor::AKHANDLE);
        if (!$ak) {
            $ak = new FileKey();
            $ak->setAttributeKeyHandle(PdfExtractor::AKHANDLE);
            $ak->setAttributeKeyName('PDF text');
            $ak->setIsAttributeKeySearchable(true);
            $ak->setIsAttributeKeyContentIndexed(true);
            $category->add('textarea', $ak, null, $pkg);
        }

        return $pkg;
    }

    public function on_start()
    {
        /** @var Repository $config */
        $config = $this->app->make('config');
        $processors = $config->get('app.import_processors');
        $processors['md.file.pdf_extractor'] = PdfExtractor::class;
        $config->set('app.import_processors', $processors);

        $this->registerAutoload();
    }

    protected function registerAutoload()
    {
        require $this->getPackagePath() . '/vendor/autoload.php';
    }
}
