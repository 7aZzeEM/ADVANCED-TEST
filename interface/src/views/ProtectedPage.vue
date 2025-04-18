<template>
  <div class="container" v-if="isLoading">
    <h1>Welcome {{ name }}!</h1>
    <p>This Page Protected With JWT.</p>
    <p>Your role: {{ role ? "Admin" : "User" }}</p>
    <p>JWT Creation Time: {{ new Date(creationTime * 1000) }}</p>
    <p>JWT Expiration Time: {{ new Date(expirationTime * 1000) }}</p>
    <button @click="Logout">Logout</button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import axios from "axios";

export default defineComponent({
  async created() {
    const token = localStorage.getItem("token");
    if (!token) {
      await this.$router.push("/");
      return;
    }
    try {
      const response = await axios.get(
        "http://localhost/server/api/verify-token",
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      if (!response.data.valid) {
        localStorage.removeItem("token");
        await this.$router.push("/");
        return;
      }
    } catch (error) {
      console.log("Token invalid or error", error);
      localStorage.removeItem("token");
      await this.$router.push("/");
      return;
    }
    this.getPayload(token);
  },
  data() {
    return {
      isLoading: false as boolean,
      name: "" as string,
      role: false as boolean,
      creationTime: "" as string,
      expirationTime: "" as string,
      isLogOut: false as boolean,
    };
  },
  methods: {
    async getPayload(token: string) {
      try {
        const response = await axios.get(
          "http://localhost/server/api/get-payload",
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        if (response.data.payload) {
          this.name = response.data.payload.username;
          this.role = response.data.payload.role;
          this.creationTime = response.data.payload.iat;
          this.expirationTime = response.data.payload.exp;
        } else {
          throw new Error("No Payload In Response!");
        }
      } catch (error) {
        console.log("Token invalid or error", error);
        localStorage.removeItem("token");
        await this.$router.push("/");
        return;
      }
      this.isLoading = true;
    },
    Logout() {
      localStorage.removeItem("token");
      this.$router.push("/");
      return;
    },
  },
  name: "ProtectedPage",
});
</script>

<style lang="scss" scoped>
@use "../style/style.scss";
.container {
  @extend %container;
}
</style>
