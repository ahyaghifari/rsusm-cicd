import './bootstrap';
import './echo';
import 'preline';
import Swiper from 'swiper/bundle';
import { Viewer as PSVViewer } from '@photo-sphere-viewer/core';
import '@photo-sphere-viewer/core/index.css';

window.Swiper = Swiper;
window.PSVViewer = PSVViewer;

document.addEventListener('livewire:navigated', () => {
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }
});
