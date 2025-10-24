<template>
  <form @submit.prevent="submit" class="space-y-4 max-w-md mx-auto">
    <h2 class="text-xl font-semibold">Sign in</h2>
    <div>
      <label class="block mb-1">Username</label>
      <input v-model="form.username" type="text" class="input" placeholder="username" />
      <p v-if="errors.username" class="error">{{ errors.username }}</p>
    </div>
    <div>
      <label class="block mb-1">Password</label>
      <input v-model="form.password" type="password" class="input" placeholder="********" />
      <p v-if="errors.password" class="error">{{ errors.password }}</p>
    </div>
    <button :disabled="loading" class="btn w-full">
      {{ loading ? 'Signing in…' : 'Sign in' }}
    </button>
    <p v-if="serverMessage" class="mt-2 text-red-600">{{ serverMessage }}</p>
  </form>
</template>

  <script setup>
  import { ref } from 'vue'
  import axios from '@/axios'
  import { useLoginRepository } from '@/stores/LoginRepository'

  const loginStore = useLoginRepository()

  const form = ref({
    username: '',
    password: '',
  })

  const loading = ref(false)
  const serverMessage = ref('')
  const errors = ref({})

  async function submit() {
    errors.value = {}
    serverMessage.value = ''
    loading.value = true
    try {
      await loginStore.Login({
        username: form.value.username,
        password: form.value.password,
      })
    } catch (err) {
      if (err.response?.status === 422) {
        const e = err.response.data.errors
        errors.value = Object.fromEntries(
          Object.entries(e).map(([k, v]) => [k, Array.isArray(v) ? v[0] : String(v)])
        )
      } else {
        serverMessage.value = 'Login failed.'
      }
    } finally {
      loading.value = false
    }
  }
  </script>

  <style scoped>
  .input { width: 100%; border: 1px solid #ddd; border-radius: 6px; padding: 8px; }
  .btn { padding: 10px 16px; border-radius: 8px; border: 1px solid #ccc; background: #f3f3f3; }
  .error { color: #dc2626; font-size: 0.9rem; }
  </style>
