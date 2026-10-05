// This script defines a hidden CSRF token input field.
// It ensures secure form submissions by including the CSRF token.
<script setup>
    const csrf = window.csrf_token;
</script>

<template>
    <input type="hidden" name="_token" :value="csrf">
</template>