import './bootstrap';
import 'preline';
import Swiper from 'swiper/bundle';

window.Swiper = Swiper;

document.addEventListener('livewire:navigated', () => {
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }
});
