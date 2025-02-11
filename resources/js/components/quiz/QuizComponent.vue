<template>
    <div class="flex flex-col items-center justify-center h-screen p-4 bg-gray-100 dark:bg-gray-900">
      <div class="relative w-full max-w-4xl p-4 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Timer and Video Preview -->
        <div class="flex items-center justify-between mb-4">
          <div class="text-xl font-bold">
            Time Remaining: {{ formatTime(timeRemaining) }}
          </div>
          <div class="bg-black rounded-lg overflow-hidden w-80 h-60">
            <video
              ref="videoPreview"
              class="w-full h-full object-cover"
              autoplay
              muted
            ></video>
          </div>
        </div>

        <!-- Question Display -->
        <div class="p-6 space-y-6 rounded-lg shadow-lg bg-blue-50 dark:bg-blue-900">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            Question {{ currentQuestion + 1 }} of {{ initialQuestions.length }}:
            {{ initialQuestions[currentQuestion].question }}
          </h3>

          <!-- Multiple Choice Questions -->
          <div v-if="initialQuestions[currentQuestion].question_type === 'multiple_choice'" class="space-y-2">
            <label
              v-for="(option, key) in JSON.parse(initialQuestions[currentQuestion].options)"
              :key="key"
              class="block p-3 bg-white rounded-lg shadow-md dark:bg-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              <input
                type="radio"
                :name="'question' + currentQuestion"
                :value="key"
                :checked="userAnswers[currentQuestion] === key"
                @change="selectAnswer(key)"
                class="mr-2"
              >
              {{ key }}. {{ option }}
            </label>
          </div>

          <!-- True/False Questions -->
          <div v-else-if="initialQuestions[currentQuestion].question_type === 'true_false'" class="space-y-2">
            <label
              v-for="option in ['true', 'false']"
              :key="option"
              class="block p-3 bg-white rounded-lg shadow-md dark:bg-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              <input
                type="radio"
                :name="'question' + currentQuestion"
                :value="option"
                :checked="userAnswers[currentQuestion] === option"
                @change="selectAnswer(option)"
                class="mr-2"
              >
              {{ option.charAt(0).toUpperCase() + option.slice(1) }}
            </label>
          </div>

          <!-- Open Ended Questions -->
          <div v-else-if="initialQuestions[currentQuestion].question_type === 'open_ended'" class="space-y-2">
            <textarea
              :value="userAnswers[currentQuestion]"
              @input="selectAnswer($event.target.value)"
              rows="4"
              class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-700 dark:text-gray-200"
              placeholder="Type your answer here..."
            ></textarea>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-between mt-6">
            <button
              @click="previousQuestion"
              :disabled="currentQuestion === 0"
              class="px-4 py-2 font-semibold text-gray-600 transition bg-gray-200 rounded-lg shadow enabled:hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-600 dark:text-gray-200"
            >
              Previous
            </button>

            <button
              @click="currentQuestion === initialQuestions.length - 1 ? submitQuiz() : nextQuestion()"
              :disabled="isSubmitting"
              class="px-4 py-2 font-semibold text-white transition bg-blue-500 rounded-lg shadow-xl hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-700"
            >
              <span v-if="isSubmitting">
                Submitting...
              </span>
              <span v-else>
                {{ currentQuestion === initialQuestions.length - 1 ? 'Submit' : 'Next' }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
<script>
    // QuizComponent.vue
import { ref, onMounted, watch } from 'vue'

export default {
  props: {
    initialQuestions: {
      type: Array,
      required: true
    },
    examId: {
      type: String,
      required: true
    },
    duration: {
      type: Number,
      required: true
    },
    studentId: {
      type: String,
      required: true
    }
  },

  setup(props) {
    const currentQuestion = ref(0)
    const userAnswers = ref({})
    const timeRemaining = ref(props.duration * 60)
    const isRecording = ref(false)
    const mediaRecorder = ref(null)
    const recordedChunks = ref([])
    const videoStream = ref(null)
    const isSubmitting = ref(false)
    const videoPreview = ref(null)

    // Local storage keys
    const STORAGE_KEY = `quiz_state_${props.examId}_${props.studentId}`
    const TIMER_KEY = `quiz_timer_${props.examId}_${props.studentId}`

    // Initialize state from localStorage
    const initializeState = () => {
      const savedState = localStorage.getItem(STORAGE_KEY)
      const savedTimer = localStorage.getItem(TIMER_KEY)

      if (savedState) {
        const state = JSON.parse(savedState)
        currentQuestion.value = state.currentQuestion
        userAnswers.value = state.userAnswers
      }

      if (savedTimer) {
        timeRemaining.value = parseInt(savedTimer)
      }
    }

    // Save state to localStorage
    const saveState = () => {
      const state = {
        currentQuestion: currentQuestion.value,
        userAnswers: userAnswers.value
      }
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
      localStorage.setItem(TIMER_KEY, timeRemaining.value.toString())
    }

    // Timer functionality
    const startTimer = () => {
      const timerInterval = setInterval(() => {
        if (timeRemaining.value <= 0) {
          clearInterval(timerInterval)
          submitQuiz()
        } else {
          timeRemaining.value--
          localStorage.setItem(TIMER_KEY, timeRemaining.value.toString())
        }
      }, 1000)

      // Cleanup on component unmount
      onUnmounted(() => {
        clearInterval(timerInterval)
        saveState()
      })
    }

    // Camera handling
    const initializeCamera = async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: true,
          audio: true
        })

        videoStream.value = stream
        if (videoPreview.value) {
          videoPreview.value.srcObject = stream
        }

        mediaRecorder.value = new MediaRecorder(stream)

        mediaRecorder.value.ondataavailable = (event) => {
          if (event.data.size > 0) {
            recordedChunks.value.push(event.data)
          }
        }

        mediaRecorder.value.start(1000)
        isRecording.value = true

        // Save chunks periodically to prevent memory issues
        setInterval(() => {
          if (recordedChunks.value.length > 10) {
            const blob = new Blob(recordedChunks.value, { type: 'video/webm' })
            saveRecording(blob)
            recordedChunks.value = []
          }
        }, 10000)

      } catch (error) {
        console.error('Camera access error:', error)
        isRecording.value = false
      }
    }

    // Save recording chunks
    const saveRecording = async (blob) => {
      const formData = new FormData()
      formData.append('recording', blob)
      formData.append('examId', props.examId)
      formData.append('studentId', props.studentId)

      try {
        await fetch('/api/save-recording', {
          method: 'POST',
          body: formData
        })
      } catch (error) {
        console.error('Error saving recording:', error)
      }
    }

    // Question navigation
    const nextQuestion = () => {
      if (currentQuestion.value < props.initialQuestions.length - 1) {
        currentQuestion.value++
        saveState()
      }
    }

    const previousQuestion = () => {
      if (currentQuestion.value > 0) {
        currentQuestion.value--
        saveState()
      }
    }

    // Answer handling
    const selectAnswer = (answer) => {
      userAnswers.value[currentQuestion.value] = answer
      saveState()
    }

    // Final submission
    const submitQuiz = async () => {
      isSubmitting.value = true

      try {
        // Stop recording
        if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
          mediaRecorder.value.stop()
          const finalBlob = new Blob(recordedChunks.value, { type: 'video/webm' })
          await saveRecording(finalBlob)
        }

        // Clean up video stream
        if (videoStream.value) {
          videoStream.value.getTracks().forEach(track => track.stop())
        }

        // Submit to Livewire
        await window.Livewire.dispatch('submitQuiz', {
          answers: userAnswers.value,
          examId: props.examId,
          studentId: props.studentId
        })

        // Clear local storage
        localStorage.removeItem(STORAGE_KEY)
        localStorage.removeItem(TIMER_KEY)

      } catch (error) {
        console.error('Submission error:', error)
      } finally {
        isSubmitting.value = false
      }
    }

    // Initialize on component mount
    onMounted(() => {
      initializeState()
      initializeCamera()
      startTimer()

      // Save state before page unload
      window.addEventListener('beforeunload', saveState)
    })

    // Watch for changes that require state save
    watch([currentQuestion, userAnswers], saveState)

    return {
      currentQuestion,
      userAnswers,
      timeRemaining,
      isRecording,
      videoPreview,
      isSubmitting,
      nextQuestion,
      previousQuestion,
      selectAnswer,
      submitQuiz
    }
  }
}
</script>
