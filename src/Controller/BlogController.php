<?php

namespace App\Controller;

use App\Repository\ArticlesRepository;
use App\Repository\BlogRepository;
use App\Repository\LangRepository;
use App\Service\breadcrumbsHelper;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BlogController extends AbstractController
{

    private $langRepository;
    private $breadcrumbsHelper;
    private $blogRepository;
    private $paginator;
    private $articlesRepository;

    public function __construct (
        LangRepository $langRepository, 
        breadcrumbsHelper $breadcrumbsHelper, 
        BlogRepository $blogRepository,
        PaginatorInterface $paginator,
        ArticlesRepository $articlesRepository)
    {
        $this->langRepository = $langRepository;
        $this->blogRepository = $blogRepository;
        $this->breadcrumbsHelper = $breadcrumbsHelper;
        $this->paginator = $paginator;
        $this->articlesRepository = $articlesRepository;
    }
    /**
    * @Route({
    *            "en": "{_locale}/blog/",
    *            "fr": "{_locale}/blog/",
    *            "es": "{_locale}/blog/",
    *        }, 
    *            name = "blog",
    *            requirements={"_locale"="^[a-z]{2}$"}
    * )
    */
    public function index(
        Request $request,
        string $locale = "es",
        string $_locale = null
    ): Response
    {
        $locale = $_locale ? $_locale : $locale;
        $this->breadcrumbsHelper->blogBreacrumbs();
        //LANG MENU
        $otherLangsArray = $this->langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $i++;
        }
        // END LANG MENU

        $articles = $this->paginator->paginate(
            $this->articlesRepository->listByLang($locale),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('blog/index.html.twig', [
            'langs' => $urlArray,
            'articles' => $articles
        ]);
    }
    /**
     * @Route({
     *          "en":"/{_locale}/blog/{article}",
     *          "es":"/{_locale}/blog/{article}",
     *          "fr":"/{_locale}/blog/{article}",
     *          },
     *          name="blog-article",
     *          requirements={"_locale"="^[a-z]{2}$"}
     * )
     */

    public function blogArticle(
                string $slug,
                string $_locale = null, 
                $locale = 'es'){
            
        $locale = $_locale ? $_locale : $locale;
        
        //OTHER LANG MENU
        $otherLangsArray = $this->langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $i++;
        }
        //END OTHER LANG MENU
        $article = $this->articlesRepository->showArticleFromSlug($slug);
        //BREADCRUMBS
        
        //$breadcrumbs->addItem($this->translatorInterface->trans("Inicio"), $this->generateUrl("index"));
        //END BREADCRUMBS
        $articleObject = $this->articlesRepository->find($article['id']);
        $this->breadcrumbsHelper->blogArticleBreadcrumbs($articleObject, $slug);
        return $this->render('blog/article.html.twig',[
            'locale' => $locale,
            'article' => $articleObject,
            'langs'=> $urlArray
        ]);

    }
}
