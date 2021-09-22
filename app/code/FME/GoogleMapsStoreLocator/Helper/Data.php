<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FME\GoogleMapsStoreLocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED                      =   'googlemapsstorelocator/general/enable';
    const XML_GMAP_PAGE_TITLE                   =   'googlemapsstorelocator/general/page_title';
    const XML_GMAP_IDENTIFIER                   =   'googlemapsstorelocator/general/identifier';
    const XML_GMAP_PAGE_METAKEYWORD             =   'googlemapsstorelocator/general/meta_keywords';
    const XML_GMAP_PAGE_METADESCRIPTION         =   'googlemapsstorelocator/general/meta_description';
    const XML_GMAP_STANDARD_LATITUDE            =   'googlemapsstorelocator/general/std_latitude';
    const XML_GMAP_STANDARD_LONGITUDE           =   'googlemapsstorelocator/general/std_longitude';
    const XML_GMAP_STANDARD_STORETITLE          =   'googlemapsstorelocator/general/std_strtitle';
    const XML_GMAP_STANDARD_DESCRIPTION         =   'googlemapsstorelocator/general/std_strdescription';
    const XML_GMAP_PAGE_HEADING                 =   'googlemapsstorelocator/general/page_heading';
    const XML_GMAP_PAGE_SUBHEADING              =   'googlemapsstorelocator/general/page_subheading';
    const XML_GMAP_API_KEY                      =   'googlemapsstorelocator/general/api_key';
    const XML_GMAP_HEADERLINK_ENABLE            =   'googlemapsstorelocator/manage_links/link_enable';
    const XML_GMAP_HEADERLINK_TEXT              =   'googlemapsstorelocator/manage_links/label_header';
    const XML_GMAP_FOOTERLINK_ENABLE            =   'googlemapsstorelocator/manage_links/footer_link_enable';
    const XML_GMAP_FOOTERLINK_TEXT              =   'googlemapsstorelocator/manage_links/label_footer';
    const XML_GMAP_SEO_IDENTIFIER               =   'googlemapsstorelocator/seo_suffix/gmap_identifier';
    const XML_GMAP_SEO_SUFFIX                   =   'googlemapsstorelocator/seo_suffix/url_suffix';
    const XML_GMAP_ZOOM                         =   'googlemapsstorelocator/general/map_zoom';

    /**
     * @return bool
     */
    public function isEnabledInFrontend()
    {
         $isEnabled = true;
         $enabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
         return $isEnabled;
    }

    /**
     * @return mixed
     */
    public function getGMapPageTitle()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_PAGE_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapMetaKeywords()
    {

        return $this->scopeConfig->getValue(self::XML_GMAP_PAGE_METAKEYWORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapMetadescription()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_PAGE_METADESCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapStandardLatitude()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_STANDARD_LATITUDE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapStandardLongitude()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_STANDARD_LONGITUDE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapStandardTitle()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_STANDARD_STORETITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapStandardDescription()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_STANDARD_DESCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapPageHeading()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_PAGE_HEADING, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapPageSubheading()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_PAGE_SUBHEADING, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapAPIKey()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_API_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function isHeaderLinkEnable()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_HEADERLINK_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getHeaderLinkLabel()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_HEADERLINK_TEXT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function isFooterLinkEnable()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_FOOTERLINK_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getFooterLinkLabel()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_FOOTERLINK_TEXT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapSeoSuffix()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_SEO_SUFFIX, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGMapSeoIdentifier()
    {
            return $this->scopeConfig->getValue(self::XML_GMAP_SEO_IDENTIFIER, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getGMapLink()
    {
        $identifier = $this->getGMapSeoIdentifier();
        $seo_suffix = $this->getGMapSeoSuffix();
        return $identifier.$seo_suffix;
    }

    /**
     * @return int|mixed
     */
    public function getGMapZoom()
    {
        if (self::XML_GMAP_ZOOM =='') {
            return 8;
        }
        return $this->scopeConfig->getValue(self::XML_GMAP_ZOOM, ScopeInterface::SCOPE_STORE);
    }
}
