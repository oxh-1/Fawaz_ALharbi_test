<template>
  <div :class="['signup-container', { dark: isDarkMode }]">
    <div class="side-panel">
      <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
      <h1>Gittax</h1>
    </div>
    <div class="form-container">
      <h2>{{ $t('signup.title') }}</h2>
      <p>{{ $t('signup.haveAccount') }} <router-link to="/login">{{ $t('signup.login') }}</router-link></p>
      <form @submit.prevent="signUp">
        <div class="form-group">
          <label for="username">{{ $t('signup.username') }}</label>
          <input type="text" v-model="username" required />
        </div>
        <div class="form-group">
          <label for="email">{{ $t('signup.email') }}</label>
          <input type="email" v-model="email" required />
        </div>
        <div class="form-group">
          <label for="password">{{ $t('signup.password') }}</label>
          <input type="password" v-model="password" required />
        </div>
        <div class="form-group">
          <label for="confirmPassword">{{ $t('signup.confirmPassword') }}</label>
          <input type="password" v-model="confirmPassword" required />
        </div>
        <button type="submit">{{ $t('signup.signUpButton') }}</button>
        <router-link to="/" class="back-home">{{ $t('signup.backHome') }}</router-link>
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
  name: 'SignUp',
  data() {
    return {
      username: '',
      email: '',
      password: '',
      confirmPassword: ''
    };
  },
  computed: {
    ...mapState(['locale', 'isDarkMode'])
  },
  methods: {
    ...mapActions(['setLocale', 'toggleDarkMode']),
    signUp() {
      if (this.password !== this.confirmPassword) {
        alert('Passwords do not match');
        return;
      }
      const users = JSON.parse(localStorage.getItem('users')) || [];
      const userExists = users.some(user => user.email === this.email);
      if (userExists) {
        alert('User already exists');
        return;
      }
      const newUser = {
        username: this.username,
        email: this.email,
        password: this.password
      };
      users.push(newUser);
      localStorage.setItem('users', JSON.stringify(users));
      alert('User registered successfully');
      this.$router.push('/login');
    },
    setLocale(locale) {
      this.$i18n.locale = locale;
    },
    toggleDarkMode() {
      this.$store.dispatch('toggleDarkMode');
    }
  }
};
</script>

<style scoped>
.signup-container {
  display: flex;
  height: 100vh;
}

.signup-container.dark {
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

.logo {
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
