<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Model\Checker;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Scans CMS pages and product descriptions for WCAG 2.1 accessibility issues:
 * - Missing alt attributes on <img> tags (WCAG 1.1.1)
 * - Heading hierarchy skips, e.g. h1 -> h3 without h2 (WCAG 1.3.1)
 * - Links with no accessible name (WCAG 4.1.2)
 */
class ContentChecker
{
    public function __construct(
        private readonly PageCollectionFactory    $pageCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory
    ) {
    }

    public function checkAll(): array
    {
        return [
            'cms_pages' => $this->checkCmsPages(),
            'products'  => $this->checkProducts(),
        ];
    }

    public function checkCmsPages(): array
    {
        $issues = [];
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToSelect(['title', 'identifier', 'content']);

        foreach ($collection as $page) {
            $pageIssues = $this->analyzeHtml((string) $page->getContent());
            if (!empty($pageIssues)) {
                $issues[] = [
                    'type'       => 'cms_page',
                    'name'       => $page->getTitle(),
                    'identifier' => $page->getIdentifier(),
                    'issues'     => $pageIssues,
                ];
            }
        }

        return $issues;
    }

    public function checkProducts(): array
    {
        $issues = [];
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku', 'description', 'short_description']);
        $collection->setPageSize(200);

        foreach ($collection as $product) {
            $html = trim(
                ((string) $product->getDescription()) . ' ' .
                ((string) $product->getShortDescription())
            );
            if ($html === '') {
                continue;
            }
            $productIssues = $this->analyzeHtml($html);
            if (!empty($productIssues)) {
                $issues[] = [
                    'type'       => 'product',
                    'name'       => $product->getName(),
                    'identifier' => $product->getSku(),
                    'issues'     => $productIssues,
                ];
            }
        }

        return $issues;
    }

    private function analyzeHtml(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<meta charset="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $issues = [];

        // WCAG 1.1.1 – Images must have alt attribute
        foreach ($dom->getElementsByTagName('img') as $img) {
            if (!$img->hasAttribute('alt')) {
                $src = $img->getAttribute('src') ?: '(no src)';
                $issues[] = [
                    'severity' => 'error',
                    'rule'     => 'WCAG 1.1.1',
                    'message'  => sprintf('Image is missing alt attribute: %s', $src),
                ];
            }
        }

        // WCAG 1.3.1 – Heading hierarchy must not skip levels
        $issues = array_merge($issues, $this->checkHeadingHierarchy($dom));

        // WCAG 4.1.2 – Links must have an accessible name
        foreach ($dom->getElementsByTagName('a') as $link) {
            $text      = trim((string) $link->textContent);
            $ariaLabel = trim((string) $link->getAttribute('aria-label'));
            $title     = trim((string) $link->getAttribute('title'));
            if ($text === '' && $ariaLabel === '' && $title === '') {
                $href = $link->getAttribute('href') ?: '(no href)';
                $issues[] = [
                    'severity' => 'error',
                    'rule'     => 'WCAG 4.1.2',
                    'message'  => sprintf(
                        'Link has no accessible name (no text, aria-label or title): %s',
                        $href
                    ),
                ];
            }
        }

        return $issues;
    }

    private function checkHeadingHierarchy(\DOMDocument $dom): array
    {
        $issues   = [];
        $levels   = [];
        $xpath    = new \DOMXPath($dom);
        $headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');

        foreach ($headings as $node) {
            $levels[] = (int) substr($node->nodeName, 1);
        }

        for ($i = 1, $count = count($levels); $i < $count; $i++) {
            $prev = $levels[$i - 1];
            $curr = $levels[$i];
            if ($curr > $prev + 1) {
                $issues[] = [
                    'severity' => 'warning',
                    'rule'     => 'WCAG 1.3.1',
                    'message'  => sprintf(
                        'Heading hierarchy skip: h%d followed by h%d (h%d is missing)',
                        $prev,
                        $curr,
                        $prev + 1
                    ),
                ];
            }
        }

        return $issues;
    }
}
