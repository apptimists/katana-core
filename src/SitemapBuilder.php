<?php

namespace Katana;

use Symfony\Component\Finder\SplFileInfo;
use Katana\Filesystem;
use Illuminate\View\Factory;
use Illuminate\Support\Str;

class SitemapBuilder
{
    private $filesystem;
    private $viewFactory;
    private $viewsData;

    /**
     * SitemapBuilder constructor.
     *
     * @param Filesystem $filesystem
     * @param Factory $viewFactory
     * @param array $viewsData
     */
    public function __construct(Filesystem $filesystem, Factory $viewFactory, array $viewsData)
    {
        $this->filesystem = $filesystem;
        $this->viewFactory = $viewFactory;
        $this->viewsData = $viewsData;
    }

    /**
     * Build blog Sitemap file.
     *
     * @return void
     */
    public function build()
    {
        if (! $view = $this->getSitemapView()) {
            return;
        }

        // Filter out some compiled sites for generating the sitemap.
        $files = array_filter($this->filesystem->allFiles(KATANA_PUBLIC_DIR), function (SplFileInfo $file) {
            $path = $file->getRelativePathName();
            return Str::endsWith($path, '.html') && !Str::startsWith($path, 'blog-page') && !Str::startsWith($path, 'sitemap') && !Str::startsWith($path, 'feed');
        });

        // Only get the relative path of the files.
        $sites = array_map(function($site) {
          return str_replace('index.html', '', $site->getRelativePathName());
        }, $files);

        $pageContent = $this->viewFactory->make($view, $this->viewsData + ['sites' => (array)$sites])->render();

        $this->filesystem->put(
            sprintf('%s/%s', KATANA_PUBLIC_DIR, 'sitemap.xml'),
            $pageContent
        );
    }

    /**
     * Get the name of the view to be used for generating the Sitemap.
     *
     * @return mixed
     * @throws \Exception
     */
    private function getSitemapView()
    {
        if (! isset($this->viewsData['sitemapView']) || ! @$this->viewsData['sitemapView']) {
            return null;
        }

        if (! $this->viewFactory->exists($this->viewsData['sitemapView'])) {
            throw new \Exception(sprintf('The "%s" view is not found. Make sure the rssFeedView configuration key is correct.', $this->viewsData['rssFeedView']));
        }

        return $this->viewsData['sitemapView'];
    }
}
