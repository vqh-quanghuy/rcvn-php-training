<template>
  <v-app>
    <v-row justify="center" align="center">
      <v-col md="5">
        <v-card class="elevation-6">
          <v-row justify="center" class="pt-2 px-2">
            <v-img src="RiverCrane-Vietnam-Logo.png" max-width="220px"></v-img>
          </v-row>
          <v-card-title>Login form</v-card-title>
          <v-alert
            class="mx-2 error--text"
            v-model="alert"
            dismissible
            color="error"
            border="left"
            elevation="1"
            colored-border
            icon="mdi-alert-outline"
            @input="closeAlert()"
          >
            <b>{{alertMesg}}</b>
          </v-alert>
          <v-card-text>
            <v-form ref="loginForm">
              <v-text-field
                v-model="email"
                prepend-icon="mdi-account-circle"
                name="email"
                label="Email"
                type="text"
                validate-on-blur
                :rules="emailRules"
              ></v-text-field>
              <v-text-field
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="showPassword = !showPassword"
                v-model="password"
                id="password"
                prepend-icon="mdi-lock"
                name="password"
                label="Password"
                :type="showPassword ? 'text' : 'password'"
                validate-on-blur
                :rules="passwordRules"
              ></v-text-field>
              <v-checkbox
                v-model="isRemember"
                label="Remember me"
              ></v-checkbox>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <!-- <v-spacer></v-spacer> -->
            <v-btn x-large class="mx-auto mb-2" color="primary" @click="loginSubmit()">
              <v-icon left>
                mdi-login
              </v-icon>
              Sign in
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-app>
</template>

<script>
export default {
  name: 'LoginView',
  data() {
    return {
      email: '',
      password: '',
      showPassword: false,
      isRemember: false,
      emailRules: [
        v => !!v || 'Email không được để trống.',
        v => (v && v.length <= 255) || 'Email phải ít hơn 255 ký tự.',
        v => /^(([^<>()[\]\\.,;:\s@']+(\.[^<>()\\[\]\\.,;:\s@']+)*)|('.+'))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(v) || 'Email không đúng định dạng.',
      ], 
      passwordRules: [
        v => !!v || 'Mật khẩu không được để trống.',
        v => (v && v.length >= 8) || 'Mật khẩu phải lớn hơn 8 ký tự.',
      ],
      alert: false,
      alertMesg: ''
    }
  },
  methods: {
    async loginSubmit() {
      if(!this.$refs.loginForm.validate()) return;
      let formData = {
        email: this.email,
        password: this.password,
        remember: this.isRemember ? 1 : 0
      }
      await this.$axios
      .post(`${this.$backendUrl}user/auth/login`, formData)
      .then(res => {
        if (res.status === 200) {
          let access_token = res.data.access_token;
          let user_info = res.data.user;

          sessionStorage.setItem("access_token", access_token);
          sessionStorage.setItem("user_info", JSON.stringify(user_info));
          this.$router.push('home') 
        }
      })
      .catch(err => {
        this.alert = true;
        this.alertMesg = err.response.data.message;
      })
    },
    closeAlert() {
      this.alert = false;
      this.alertMesg = '';
    }
  },
  mounted() {
    if(sessionStorage.getItem("access_token") !== null) this.$router.push('home') 
  }
};
</script>

<style></style>
