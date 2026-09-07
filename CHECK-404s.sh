#!/bin/bash
# Run this in your browser's developer console or save as bookmarklet
# It will check all images on the current page for 404 errors

console.log("=== Checking for broken images ===");
const images = document.querySelectorAll('img');
let broken = 0;
images.forEach(img => {
  if (img.naturalWidth === 0) {
    console.error("BROKEN:", img.src);
    broken++;
  }
});
if (broken === 0) {
  console.log("✅ All images loaded successfully");
} else {
  console.log(`❌ Found ${broken} broken images`);
}
