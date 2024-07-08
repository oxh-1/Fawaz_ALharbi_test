<template>
  <div :class="['login-container', { dark: isDarkMode }]">
    <div class="side-panel">
      <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
      <h1>Gittax</h1>
    </div>
    <div class="form-container">
      <h2>{{ $t('login.title') }}</h2>
      <p>{{ $t('login.noAccount') }} <router-link to="/signup">{{ $t('login.signUp') }}</router-link></p>
      <form @submit.prevent="login">
        <div class="form-group">
          <label for="email">{{ $t('login.email') }}</label>
          <input type="email" v-model="email" required />
        </div>
        <div class="form-group">
          <label for="password">{{ $t('login.password') }}</label>
          <input type="password" v-model="password" required />
        </div>
        <button type="submit">{{ $t('login.loginButton') }}</button>
        <router-link to="/forgot-password" class="forgot-password">{{ $t('login.forgotPassword') }}</router-link>
        <button class="google-login">{{ $t('login.googleLogin') }}</button>
        <router-link to="/" class="back-home">{{ $t('login.backHome') }}</router-link>
      </form>
      <div class="language-switcher">
        <button @click="setLocale('en')">EN</button>
        <button @click="setLocale('ar')">AR</button>
      </div>
      <div class="mode-toggle">
        <label>{{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}</label>
        <input type="checkbox" v-model="isDarkMode" @change="toggleDarkMode" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'LoginPage',
  data() {
    return {
      email: '',
      password: ''
    };
  },
  computed: {
    ...mapState(['locale', 'isDarkMode'])
  },
  methods: {
    ...mapActions(['setLocale', 'toggleDarkMode']),
    login() {
      const users = JSON.parse(localStorage.getItem('users')) || [];
      const user = users.find(user => user.email === this.email && user.password === this.password);
      if (user) {
        localStorage.setItem('loggedInUser', JSON.stringify(user));
        this.$router.push('/dashboard');
      } else {
        alert('Invalid email or password');
      }
    }
  }
};
</script>

<style scoped>
.login-container {
  display: flex;
  height: 100vh;
}

.login-container.dark {
  background-color: #121212;
  color: #fff;
}

.side-panel {
  width: 50%;
  height: 100vh;
  background: linear-gradient(102.16deg, #303030 -78.58%, rgba(48, 48, 48, 0) 278.09%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.side-panel img {
  width: 80px;
  height: 80px;
  margin-bottom: 20px;
}

.form-container {
  width: 50%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 20px;
  box-sizing: border-box;
}

form {
  width: 100%;
  max-width: 400px;
}

h2 {
  margin-bottom: 10px;
}

p {
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
}

.form-group input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button[type="submit"] {
  width: 100%;
  padding: 10px;
  background-color: #ffcc00;
  border: 1px solid #ffcc00;
  border-radius: 4px;
  cursor: pointer;
  margin-bottom: 15px;
}

button.google-login {
  width: 100%;
  padding: 10px;
  background-color: #4285f4;
  border: 1px solid #4285f4;
  border-radius: 4px;
  cursor: pointer;
  color: white;
  margin-bottom: 15px;
}

.forgot-password {
  display: block;
  text-align: right;
  margin-bottom: 15px;
}

.back-home {
  display: block;
  text-align: center;
  margin-top: 10px;
  color: #ff5722;
}

.language-switcher {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.language-switcher button {
  background: none;
  border: none;
  color: inherit;
  margin: 0 10px;
  cursor: pointer;
  font-size: 16px;
}

.mode-toggle {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

.mode-toggle label {
  margin-right: 10px;
}
</style>
