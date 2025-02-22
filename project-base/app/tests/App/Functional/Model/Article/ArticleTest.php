<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Article;

use App\Model\Article\Article;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface;
use Tests\App\Test\TransactionFunctionalTestCase;

class ArticleTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ArticleDataFactory $articleDataFactory;

    /**
     * @inject
     */
    private ArticleFactoryInterface $articleFactory;

    public function testArticleIsCorrectlyRestoredFromDatabase()
    {
        $articleData = $this->articleDataFactory->create(Domain::FIRST_DOMAIN_ID);

        $articleData->name = 'Demonstrative name';
        $articleData->placement = Article::PLACEMENT_FOOTER_1;
        $articleData->seoTitle = 'Demonstrative seo title';
        $articleData->seoMetaDescription = 'Demonstrative seo description';
        $articleData->seoH1 = 'Demonstrative seo H1';
        $articleData->createdAt = new DateTime('2000-01-01T01:01:01');

        $article = $this->articleFactory->create($articleData);

        $this->em->persist($article);
        $this->em->flush();

        $articleId = $article->getId();

        $this->em->clear();

        /** @var \App\Model\Article\Article $refreshedArticle */
        $refreshedArticle = $this->em->getRepository(Article::class)->find($articleId);

        $this->assertSame('Demonstrative name', $refreshedArticle->getName());
        $this->assertSame(Article::PLACEMENT_FOOTER_1, $refreshedArticle->getPlacement());
        $this->assertSame('Demonstrative seo title', $refreshedArticle->getSeoTitle());
        $this->assertSame('Demonstrative seo description', $refreshedArticle->getSeoMetaDescription());
        $this->assertSame('Demonstrative seo H1', $refreshedArticle->getSeoH1());
        $this->assertEquals(new DateTime('2000-01-01T01:01:01'), $refreshedArticle->getCreatedAt());
    }
}
