function getCacheValue() {
  const selectedValue = document.getElementById('cache');
  return selectedValue.options[selectedValue.selectedIndex].value;
}

function displayCacheForm() {
  const classes = ['redis', 'file', 'memcache'];
  const currentValue = getCacheValue();
  const indexOfCurrent = classes.indexOf(currentValue);

  if (indexOfCurrent > -1) {
    classes.splice(indexOfCurrent, 1);
  }

  const elementsShow = document.getElementsByClassName(currentValue);

  for (let i = 0, len = elementsShow.length; i < len; i++) {
    elementsShow[i].style['display'] = 'block';
  }
  for (let i = 0, len = classes.length; i < len; i++) {
    const elementsHide = document.getElementsByClassName(classes[i]);
    for (let x = 0, len = elementsHide.length; x < len; x++) {
      elementsHide[x].style['display'] = 'none';
    }
  }
}

displayCacheForm();
