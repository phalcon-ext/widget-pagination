<?php
/**
 * PaginationWidget.php 2014-07-24 22:31
 * ----------------------------------------------
 *
 *
 * @author      Stanislav Kiryukhin <korsar.zn@gmail.com>
 * @copyright   Copyright (c) 2014, CKGroup.ru
 *
 * ----------------------------------------------
 * All Rights Reserved.
 * ----------------------------------------------
 */
namespace Phalcon\Ext\Widgets;

use Phalcon\Paginator\AdapterInterface as PaginatorInterface;

/**
 * Class PaginationWidget
 * @package Phalcon\Ext\Widgets
 */
class PaginationWidget extends WidgetBase
{
    protected $current;
    protected $before;
    protected $next;
    protected $last;
    protected $limit;
    protected $total_pages;
    protected $total_items;

    /**
     * Called after __construct
     *
     * @return void
     */
    public function onConstruct()
    {
        if (($paginateObject = $this->getOptions('paginate')) && $paginateObject instanceof PaginatorInterface) {

            $paginate = $paginateObject->getPaginate();

            $this->current      = $paginate->current;
            $this->before       = $paginate->before;
            $this->next         = $paginate->next;
            $this->last         = $paginate->last;
            $this->total_pages  = $paginate->total_pages;
            $this->total_items  = $paginate->total_items;

            $this->limit = ceil($this->total_items / $this->total_pages);
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function render(Array $params = [])
    {
        if ($this->total_pages < $this->getOptions('numVisible')) {
            return null;
        }

        $pagination = [

            'limit' => $this->limit,
            'count' => $this->total_items,
            'total' => $this->total_pages,
            'current' => $this->current,
            'next_num' => $this->next,
            'next_url' => $this->getLink($this->next),
            'prev_num' => $this->before,
            'prev_url' => $this->getLink($this->before),
            'first_num' => 1,
            'first_url' => $this->getLink(1),
            'last_num' => $this->last,
            'last_url' => $this->getLink($this->last),
            'pages' => [],
            'isFirst' => 1 == $this->current,
            'isLast' => $this->last == $this->current
        ];

        $dotted = false;

        $separator   = $this->getOptions('separator');
        $classActive = $this->getOptions('classActive');

        $c = $this->current;
        $t = $this->total_pages;
        $k = $this->getOptions('numPage');


        // Generating page list.
        for ($i = 1; $i <= $t; $i++) {

            $page = [];
            $page['url'] = $this->getLink($i);
            $page['num'] = $i;
            $page['isSeparator'] = false;
            $page['isActive'] = ($this->current == $i) ? true : false;
            $page['class'] = ($this->current == $i) ? $classActive : null;

            if (($i > $k && $i <= ($c - $k)) || ($i >= ($c + $k) && $i <= ($t - $k))) {

                if (!$dotted) {
                    $page['num'] = $separator;
                    $page['isSeparator'] = true;
                    $pagination['pages'][] = $page;
                }

                $dotted = true;
                continue;
            }

            $dotted = false;
            $pagination['pages'][] = $page;
        }

        $params['pagination'] = $pagination;
        return $this->getView()->partial('pagination', $params);
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            'paramKey'     => 'page',
            'separator'    => '...',
            'classActive'  => 'active',
            'numVisible'   => 2,
            'numPage'      => 3
        ];
    }

    /**
     * @param $page
     *
     * @return string
     */
    protected function getLink($page)
    {
        $url = $this->getOptions('url');

        if ($url == '#') {
            return $url;
        }

        $_uri = parse_url($url ?: $_SERVER['HTTP_URI']);

        if (!empty($_uri['query'])) {

            parse_str($_uri['query'], $query);
            $query[$this->getOptions('paramKey')] = $page;

            return urldecode($_uri['path'] . '?' . http_build_query($query));
        } else {
            return urldecode($url . '?' . $this->getOptions('paramKey') . '=' . $page);
        }
    }
}
