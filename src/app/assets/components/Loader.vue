<template>
<div id="loading-container">
	<div id="loading-bubble" class="grey">
		<div class="spinner">
			<div class="bounce1"></div>
			<div class="bounce2"></div>
			<div class="bounce3"></div>
		</div>
	</div>
</div>
</template>

<style>
.spinner {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 45px;
  height: 9px;
  margin-left: -22px;
  margin-top: -13px;
  text-align: center;
}

.spinner > div {
  width: 9px;
  height: 9px;
  background-color: #dcdcdc;
  border-radius: 100%;
  display: inline-block;
  -webkit-animation: bouncedelay 1.4s infinite ease-in-out;
  animation: bouncedelay 1400ms ease-in-out infinite;
  /* Prevent first frame from flickering when animation starts */
  -webkit-animation-fill-mode: both;
  animation-fill-mode: both;
}

.spinner .bounce1 {
  -webkit-animation-delay: -0.32s;
  animation-delay: -0.32s;
}

.spinner .bounce2 {
  -webkit-animation-delay: -0.16s;
  animation-delay: -0.16s;
}

@-webkit-keyframes bouncedelay {
  0%, 80%, 100% {
    transform: scale(0);
    -webkit-transform: scale(0);
  }
  40% {
    transform: scale(1);
    -webkit-transform: scale(1);
  }
}
@keyframes bouncedelay {
  0%, 80%, 100% {
    transform: scale(0);
    -webkit-transform: scale(0);
  }
  40% {
    transform: scale(1);
    -webkit-transform: scale(1);
  }
}
#loading-container {
  position: relative;
  top: 0px;
  left: 0px;
  width: 100%;
  display: flex;
  display: -ms-flexbox;
  justify-content: space-around;
  -webkit-justify-content: space-around;
  align-items: center;
  -webkit-align-items: center;
}

#loading-bubble {
  display: flex;
  display: -ms-flexbox;
  justify-content: center;
  -webkit-justify-content: center;
  align-items: center;
  -webkit-align-items: center;
  width: auto;
  height: auto;
  min-width: 140px;
  min-height: 61px;
  border-radius: 10px;
  box-sizing: border-box;
  position: relative;
  background-color: #2e66bd;
  -webkit-animation: message-bounce 2.5s infinite ease-in-out;
  animation: message-bounce 2.5s ease-in-out infinite;
  -webkit-animation-fill-mode: both;
  animation-fill-mode: both;
  right: 40%;

}
#loading-bubble.grey {
  background-color: #ccc;
}
#loading-bubble.grey:before {
  border-color: transparent #ccc transparent transparent;
}
#loading-bubble:before {
  display: block;
  content: " ";
  width: 0;
  height: 0;
  border-style: solid;
  border-width: 8px 8px 8px 0;
  border-color: transparent #2e66bd transparent transparent;
  position: absolute;
  left: -7px;
  top: 13px;
}
#loading-bubble .spinner {
  position: static !important;
  margin-top: -11px;
  margin-left: 0px;
}
#loading-bubble .spinner div {
  background-color: #fff;
}

@-webkit-keyframes message-bounce {
  0% {
    transform: scale(0.9);
    -webkit-transform: scale(0.9);
  }
  50% {
    transform: scale(1.1);
    -webkit-transform: scale(1.1);
  }
  100% {
    transform: scale(0.9);
    -webkit-transform: scale(0.9);
  }
}

@keyframes message-bounce {
  0% {
    transform: scale(0.9);
    -webkit-transform: scale(0.9);
  }
  50% {
    transform: scale(1.1);
    -webkit-transform: scale(1.1);
  }
  100% {
    transform: scale(0.9);
    -webkit-transform: scale(0.9);
  }
}
</style>
