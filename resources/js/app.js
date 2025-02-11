import './bootstrap';

import Alpine from 'alpinejs';

import { createApp } from 'vue';
import QuizComponent from './components/quiz/QuizComponent.vue';

window.Alpine = Alpine;

Alpine.start();

// Initialize Vue.js for Quiz Component
const app = createApp({});
app.component('quiz-component', QuizComponent);
app.mount("#quiz-app");
