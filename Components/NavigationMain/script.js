import Splide from "@splidejs/splide";

export default function (el) {
  const isDesktopMediaQuery = window.matchMedia('(min-width: 1024px)')
  isDesktopMediaQuery.addEventListener('change', onBreakpointChange)

  onBreakpointChange()

  function onBreakpointChange () {
    if (isDesktopMediaQuery.matches) {
      setScrollPaddingTop()
    }
  }


  console.log("jijfffffffij");

  new Splide('.matchups_slider_header',{
  
pagination:false,
    focus    : 'center',
    autoWidth: true,

  }).mount();

  function setScrollPaddingTop () {
    const scrollPaddingTop = document.getElementById('wpadminbar')
      ? document.getElementById('wpadminbar').offsetHeight
      : 0
    document.documentElement.style.scrollPaddingTop = `${scrollPaddingTop}px`
  }
}


