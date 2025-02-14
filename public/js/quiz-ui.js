export class QuizUI {
    constructor() {
        this.initializeTimers();
        this.setupQuestionNavigation();
        this.setupProgressBar();
    }

    initializeTimers() {
        this.mainTimer = new Timer('#mainTimer', {
            onTick: this.updateTimerDisplay.bind(this),
            onWarning: this.showTimeWarning.bind(this),
            onExpire: () => Livewire.dispatch('timeExpired')
        });
    }

    setupQuestionNavigation() {
        this.questionPalette = new QuestionPalette('#questionPalette', {
            onQuestionSelect: this.handleQuestionSelect.bind(this),
            onQuestionFlag: this.handleQuestionFlag.bind(this)
        });
    }

    setupProgressBar() {
        this.progressBar = new ProgressBar('#progressBar', {
            total: document.querySelectorAll('.question').length,
            current: 1
        });
    }

    showTimeWarning(timeLeft) {
        if (timeLeft <= 300) { // 5 minutes
            this.showNotification('Warning', `Only ${Math.floor(timeLeft / 60)} minutes remaining!`, 'warning');
        }
    }

    handleQuestionSelect(questionId) {
        Livewire.dispatch('questionSelected', { questionId });
    }

    handleQuestionFlag(questionId) {
        Livewire.dispatch('questionFlagged', { questionId });
    }

    showNotification(title, message, type = 'info') {
        const toast = new Toast({
            title,
            message,
            type,
            duration: 3000
        });
        toast.show();
    }
}
