<?php

namespace Knp\Menu\Tests\Renderer;

use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Renderer\PimpleProvider;
use Knp\Menu\Renderer\TwigRenderer;
use Knp\Menu\MenuItem;
use Knp\Menu\MenuFactory;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Twig\Helper;
use Knp\Menu\Twig\MenuExtension;

class TwigRendererTest extends AbstractRendererTest
{
    public function createRenderer(MatcherInterface $matcher)
    {
        if (!class_exists('Twig_Environment')) {
            $this->markTestSkipped('Twig is not available');
        }
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../../src/Knp/Menu/Resources/views');
        $environment = new \Twig_Environment($loader);
        $renderer = new TwigRenderer($environment, 'knp_menu.html.twig', $matcher, array('compressed' => true));

        return $renderer;
    }

    public function testRenderOrderedList()
    {
        $menu = new MenuItem('test', new MenuFactory());
        $menu->addChild('About')->addChild('foobar');

        $rendered = '<ol><li class="first last"><span>About</span><ol class="menu_level_1"><li class="first last"><span>foobar</span></li></ol></li></ol>';
        $this->assertEquals($rendered, $this->renderer->render($menu, array('template' => 'knp_menu_ordered.html.twig')));
    }

    public function testBlockInheritance()
    {
        $loader = new \Twig_Loader_Filesystem(array(
            __DIR__.'/../../../../../src/Knp/Menu/Resources/views',
            __DIR__.'/../Fixtures/views',
        ));
        $environment = new \Twig_Environment($loader);

        $renderer = new TwigRenderer($environment, 'knp_menu.html.twig', new Matcher(), array('compressed' => true));

        $pimple = new \Pimple();
        $pimple['twig'] = $renderer;
        $provider = new PimpleProvider($pimple, 'twig', array('twig' => 'twig'));
        $environment->addExtension(new MenuExtension(new Helper($provider)));

        $menu = new MenuItem('test', new MenuFactory());
        $menu->addChild('Child1')->addChild('Child2')->addChild('Child3')->addChild('Child4');

        $this->assertEquals('', $environment->render('header.html.twig', array('menu' => $menu)));
        $this->assertEquals('', $renderer->render($menu, array('template' => 'custom_menu.html.twig')));
    }
}
