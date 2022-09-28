<template>
  <div id="app">
    <router-view/>
  </div>
</template>
<script>
export default {
  mounted() {
    if(sessionStorage.getItem("access_token") === null && this.currentRouteName !== 'login') {
      this.$router.push('login') 
    }
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    }
  }
}
</script>
<style lang="scss">
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

nav {
  padding: 30px;

  a {
    font-weight: bold;
    color: #2c3e50;

    &.router-link-exact-active {
      color: #42b983;
    }
  }
}
</style>
