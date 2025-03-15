<template>
    <div>
      <h1>{{ mensaje }}</h1>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        mensaje: "¡Hola, mundo en Vue.js 2!"
      };
    }
  };
  </script>
  
  <style scoped>
  h1 {
    color: blue;
  }
  </style>
  