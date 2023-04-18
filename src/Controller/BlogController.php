<?php

namespace App\Controller;

use App\Repository\ArticlesRepository;
use App\Repository\BlogRepository;
use App\Repository\LangRepository;
use App\Service\breadcrumbsHelper;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BlogController extends AbstractController
{
    public function __construct(
        private LangRepository $langRepository,
        private breadcrumbsHelper $breadcrumbsHelper,
        private BlogRepository $blogRepository,
        private PaginatorInterface $paginator,
        private ArticlesRepository $articlesRepository)
    {
        $this->langRepository = $langRepository;
        $this->blogRepository = $blogRepository;
        $this->breadcrumbsHelper = $breadcrumbsHelper;
        $this->paginator = $paginator;
        $this->articlesRepository = $articlesRepository;
    }

    #[Route(
        path: '{_locale}/blog/',
        name: 'blog',
        requirements: ['_locale' => '^[a-z]{2}$'])]
    public function index(
        Request $request,
        string $_locale = 'es'
    ): Response {
        $this->breadcrumbsHelper->blogBreacrumbs();
        // LANG MENU
        $otherLangsArray = $this->langRepository->findOthers($_locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // END LANG MENU

        $articles = $this->paginator->paginate(
            $this->articlesRepository->listByLang($_locale),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('blog/index.html.twig', [
            'langs' => $urlArray,
            'articles' => $articles,
        ]);
    }

    #[Route(
        path: '/{_locale}/blog/{slug}',
        name: 'blog-article',
        requirements: ['_locale' => '^[a-z]{2}$'])]
    public function blogArticle(
                string $slug,
                string $_locale = 'es')
    {

        // OTHER LANG MENU
        $otherLangsArray = $this->langRepository->findOthers($_locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // END OTHER LANG MENU
        $article = $this->articlesRepository->showArticleFromSlug($slug);
        // BREADCRUMBS
        $articleObject = $this->articlesRepository->find($article['id']);
        $this->breadcrumbsHelper->blogArticleBreadcrumbs($articleObject, $slug);
        // END BREADCRUMBS


        return $this->render('blog/article.html.twig', [
            'locale' => $_locale,
            'article' => $articleObject,
            'langs' => $urlArray,
        ]);
    }
}
