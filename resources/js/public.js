import Alert from 'bootstrap/js/dist/alert.js';
import Collapse from 'bootstrap/js/dist/collapse.js';
import Menu from 'bootstrap/js/dist/menu.js';
import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation, Pagination, Parallax } from 'swiper/modules';

import enableAnchorTop from './public/anchor-top.ts';
import enableNavigation from './public/navigation.ts';
import enablePhotoSwipeLightbox from './public/photo-swipe-lightbox.ts';

import.meta.glob(['../images/**'], { eager: true, query: '?url' });

Swiper.use([Navigation, Pagination, Autoplay, Parallax, EffectFade]);
window.Swiper = Swiper;

enablePhotoSwipeLightbox();
enableAnchorTop();
enableNavigation();

/**
 * For TypiCMS’s Places module
 */
// import initMap from './public/map';
// window.initMap = initMap;
