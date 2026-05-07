<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api/axios'

const router = useRouter()
const loading = ref(false)

const quiz = ref({
  title: '',
  description: '',
  questions: [
    {
      question_text: '',
      points: 1,
      options: [
        { option_text: '', is_correct: true },
        { option_text: '', is_correct: false }
      ]
    }
  ]
})

const addQuestion = () => {
  quiz.value.questions.push({
    question_text: '',
    points: 1,
    options: [
      { option_text: '', is_correct: true },
      { option_text: '', is_correct: false }
    ]
  })
}

const removeQuestion = (index) => {
  quiz.value.questions.splice(index, 1)
}

const addOption = (qIndex) => {
  quiz.value.questions[qIndex].options.push({ option_text: '', is_correct: false })
}

const removeOption = (qIndex, oIndex) => {
  quiz.value.questions[qIndex].options.splice(oIndex, 1)
}

const setCorrect = (qIndex, oIndex) => {
  quiz.value.questions[qIndex].options.forEach((opt, idx) => {
    opt.is_correct = idx === oIndex
  })
}

const handleSubmit = async () => {
  loading.value = true
  try {
    await api.post('/quizzes', quiz.value)
    router.push('/quizzes')
  } catch (err) {
    alert('Failed to create quiz: ' + (err.response?.data?.message || 'Check your inputs'))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center space-x-4 mb-10">
      <router-link to="/quizzes" class="p-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <h1 class="text-4xl font-black text-slate-900">Create New Quiz</h1>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-8">
      <div class="card bg-white border-slate-100">
        <h3 class="text-xl font-bold text-slate-900 mb-6">General Information</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Quiz Title</label>
            <input v-model="quiz.title" type="text" required class="input text-lg" placeholder="e.g. Introduction to Vue 3">
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
            <textarea v-model="quiz.description" rows="3" class="input" placeholder="What will students learn from this quiz?"></textarea>
          </div>
        </div>
      </div>

      <div v-for="(question, qIndex) in quiz.questions" :key="qIndex" class="card relative overflow-visible">
        <div class="absolute -left-3 top-6 w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center font-bold text-white shadow-lg shadow-primary-500/20">
          {{ qIndex + 1 }}
        </div>
        
        <div class="flex justify-between items-start mb-6 ml-6">
          <div class="flex-grow mr-4">
            <label class="block text-sm font-medium text-gray-400 mb-2">Question Text</label>
            <input v-model="question.question_text" type="text" required class="input" placeholder="Enter your question">
          </div>
          <div class="w-24">
            <label class="block text-sm font-medium text-gray-400 mb-2">Points</label>
            <input v-model="question.points" type="number" min="1" class="input text-center">
          </div>
          <button @click="removeQuestion(qIndex)" type="button" class="ml-4 mt-8 text-gray-600 hover:text-red-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>

        <div class="space-y-3 ml-6">
          <label class="block text-sm font-medium text-gray-400">Options</label>
          <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex items-center space-x-3">
            <button @click="setCorrect(qIndex, oIndex)" type="button" 
              class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
              :class="option.is_correct ? 'bg-green-500 border-green-500 text-white' : 'border-gray-700 hover:border-gray-500'">
              <svg v-if="option.is_correct" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
            <input v-model="option.option_text" type="text" required class="input py-1.5" placeholder="Option text">
            <button v-if="question.options.length > 2" @click="removeOption(qIndex, oIndex)" type="button" class="text-gray-600 hover:text-red-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <button @click="addOption(qIndex)" type="button" class="text-primary-500 hover:text-primary-400 text-xs font-bold flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Option
          </button>
        </div>
      </div>

      <div class="flex justify-between items-center pt-6">
        <button @click="addQuestion" type="button" class="btn-secondary flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Question
        </button>
        <button type="submit" :disabled="loading" class="btn-primary px-10 py-3 text-lg">
          {{ loading ? 'Saving Quiz...' : 'Publish Quiz' }}
        </button>
      </div>
    </form>
  </div>
</template>
