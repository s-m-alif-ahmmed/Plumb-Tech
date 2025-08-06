<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to Plum Tech</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-purple-500 to-indigo-700 min-h-screen flex flex-col justify-center items-center p-4">
<div class="bg-white bg-opacity-10 backdrop-filter backdrop-blur-lg rounded-xl shadow-2xl p-8 max-w-lg w-full">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-white mb-2">Welcome</h1>
        <p class="text-lg text-white text-opacity-80 mb-8">We're excited to have you here!</p>

        <div class="flex flex-col space-y-6 mb-8">
            <div class="bg-white bg-opacity-20 p-6 rounded-lg">
                <h2 class="text-xl font-semibold text-white mb-2">Discover</h2>
                <p class="text-white text-opacity-80">Explore our platform and all it has to offer.</p>
            </div>

            <div class="bg-white bg-opacity-20 p-6 rounded-lg">
                <h2 class="text-xl font-semibold text-white mb-2">Connect</h2>
                <p class="text-white text-opacity-80">Join our community and meet like-minded people.</p>
            </div>

            <div class="bg-white bg-opacity-20 p-6 rounded-lg">
                <h2 class="text-xl font-semibold text-white mb-2">Create</h2>
                <p class="text-white text-opacity-80">Bring your ideas to life with our powerful tools.</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 justify-center">
            <button class="bg-white text-purple-700 font-medium py-3 px-6 rounded-lg hover:bg-opacity-90 transition duration-300">
                Get Started
            </button>
            <button class="bg-transparent border-2 border-white text-white font-medium py-3 px-6 rounded-lg hover:bg-white hover:bg-opacity-10 transition duration-300">
                Learn More
            </button>
        </div>
    </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded',function (){
       Echo.private('accept.4').listen('AcceptRequest',(e)=>{
           console.log(e)
       })
   })
</script>

<footer class="mt-8 text-center text-white text-opacity-70 text-sm">
    <p>© 2025 Your Company. All rights reserved.</p>
</footer>
</body>
</html>
