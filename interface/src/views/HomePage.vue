<template>
  <div class="container" v-if="showLoginForm">
    <h1>Login</h1>
    <div class="error-message" v-if="errorMessage">
      {{ errorMessage }}
    </div>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="username">Username:</label>
        <input
          type="text"
          id="username"
          v-model.trim="username"
          placeholder="Enter your username"
          autocomplete="username"
          required
          aria-describedby="username-help"
        />
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input
          type="password"
          id="password"
          v-model.trim="password"
          placeholder="Enter your password"
          autocomplete="current-password"
          required
          minlength="6"
        />
      </div>
      <button type="submit" :disabled="isSubmitting">
        <span v-if="!isSubmitting">Login</span>
        <span v-else>Logging in...</span>
      </button>
    </form>
  </div>
</template>

<script lang="ts">
type Account = {
  username: string;
  password: string;
};

import { defineComponent } from "vue";
import axios from "axios";

export default defineComponent({
  async created() {
    const token = localStorage.getItem("token");
    if (token) {
      try {
        const response = await axios.get(
          "http://localhost/server/api/verify-token",
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        if (response.data.valid) {
          await this.$router.push("/protected");
          return;
        }
      } catch (error) {
        console.log("Token invalid or error", error);
        localStorage.removeItem("token");
      }
    }
    this.showLoginForm = true;
  },
  name: "HomePage",
  data() {
    return {
      errorMessage: "" as string,
      username: "" as string,
      password: "" as string,
      isSubmitting: false as boolean,
      showLoginForm: false as boolean,
    };
  },
  methods: {
    handleSubmit() {
      if (this.isSubmitting) return;

      this.errorMessage = "";
      this.isSubmitting = true;

      if (!this.username || !this.password) {
        this.errorMessage = "Please fill in all fields";
        this.isSubmitting = false;
        return;
      }
      if (this.password.length < 6) {
        this.errorMessage = "Password must be at least 6 characters";
        this.isSubmitting = false;
        return;
      }
      const account: Account = {
        username: this.username,
        password: this.password,
      };
      axios
        .post("http://localhost/server/api/login", account)
        .then((response) => {
          if (response.data.token) {
            localStorage.setItem("token", response.data.token);
            this.$router.push("/protected");
          } else {
            console.error("Token not found in response");
          }
        })
        .catch((error) => {
          if (error.response) {
            if (error.response.status === 401) {
              this.errorMessage = "Invalid Username or Password!";
              this.isSubmitting = false;
              return;
            } else {
              this.errorMessage =
                "Something wrong with server, try again later";
              this.isSubmitting = false;
              return;
            }
          } else if (error.request) {
            this.errorMessage = "Connection server off";
            this.isSubmitting = false;
            return;
          } else {
            this.errorMessage =
              "API not send, something wrong, try again later";
            this.isSubmitting = false;
            return;
          }
        });
    },
  },
});
</script>

<style lang="scss" scoped>
@use "../style/style.scss";
.container {
  @extend %container;
}
form {
  @extend %formStyle;
}
</style>
