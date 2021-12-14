/*global $, jQuery*/

/* SETTINGS */
var i_v = {
  i_duration: window.innerHeight * 1.5, // (ms) duration of the inertial scrolling simulation. Devices with larger screens take longer durations (phone vs tablet is around 500ms vs 1500ms). This is a fixed value and does not influence speed and amount of momentum.
  i_speedLimit: 1.2,                      // set maximum speed. Higher values will allow faster scroll (which comes down to a bigger offset for the duration of the momentum scroll) note: touch motion determines actual speed, this is just a limit.
  i_handleY: true,                     // should scroller handle vertical movement on element?
  i_handleX: true,                     // should scroller handle horizontal movement on element?
  i_moveThreshold: 100,                      // (ms) determines if a swipe occurred: time between last updated movement @ touchmove and time @ touchend, if smaller than this value, trigger inertial scrolling
  i_offsetThreshold: 30,                       // (px) determines, together with i_offsetThreshold if a swipe occurred: if calculated offset is above this threshold
  i_startThreshold: 5,                        // (px) how many pixels finger needs to move before a direction (horizontal or vertical) is chosen. This will make the direction detection more accurate, but can introduce a delay when starting the swipe if set too high
  i_acceleration: 0.5,                      // increase the multiplier by this value, each time the user swipes again when still scrolling. The multiplier is used to multiply the offset. Set to 0 to disable.
  i_accelerationT: 250                       // (ms) time between successive swipes that determines if the multiplier is increased (if lower than this value)
};
/* stop editing here */

// Define easing function. This is based on a quartic 'out' curve. You can generate your own at http://www.timotheegroleau.com/Flash/experiments/easing_function_generator.htm
/**
if ($.easing.hnlinertial === undefined) {
  $.easing.hnlinertial = function (x, t, b, c, d) {
    "use strict";
    var ts = (t /= d) * t, tc = ts * t;
    return b + c * (-1 * ts * ts + 4 * tc + -6 * ts + 4 * t);
  };
}*/

const body = document.body;
let node = document.querySelector('.touchScroll');

node.addEventListener('touchstart', function (e) {
console.log(e.noTouchScroll);
    if (typeof e.noTouchScroll !== 'undefined') {
        return;
    }
console.log('START');
console.log(e);
    this.origEvent = e;

    e.stopPropagation();
    e.preventDefault();

    this.distance = 0;
    this.acc = 0;
    this.pageY = e.touches[0].pageY;
    this.pageX = e.touches[0].pageX;

    this.maxScrollPos = this.offsetHeight
        - this.offsetParent.getBoundingClientRect().height
        + this.offsetTop;
    this.scrollPosition = 0

    /** chrome66
    const attrs = node.attributeStyleMap.get('transform')
    if (attrs) {
        const translation = Array.from(attrs.values()).find(attr => attr instanceof CSSTranslate)
        if (translation) {
            this.scrollPosition = -1 * translation.y.value;
        }
    }*/

    var style = getComputedStyle(this),
        transform = this.style.transform,
        translateY = transform.match(/translateY\((.*)px\)/);

    if (translateY) {
        this.scrollPosition = -1 * translateY[1];
    }

    this.height = this.offsetHeight;
    this.timetouchstart = e.timeStamp;
});
node.addEventListener('wheel', function (e) {
    // see touchstart
    this.maxScrollPos = this.offsetHeight
        - this.offsetParent.getBoundingClientRect().height
        + this.offsetTop;
    this.scrollPosition = 0
console.log(this.style.transition);
    var transform = this.style.transform,
        translateY = transform.match(/translateY\((.*)px\)/);

    if (translateY) {
        this.scrollPosition = -1 * translateY[1];
    }

    this.distance = e.deltaY;

    // see touchmove
    let scrollTo = this.scrollPosition + this.distance;

    if (scrollTo < 0) { scrollTo = 0; }
    if (scrollTo > this.maxScrollPos) { scrollTo = this.maxScrollPos; }
    this.style.transform = 'translateY('+ (-scrollTo) +'px)';
});
node.addEventListener('touchmove', function (e) {
    this.timetouchmove = e.timeStamp;

    this.distance = this.pageY - e.touches[0].pageY;
    this.acc = Math.abs(this.distance / (this.timetouchmove - this.timetouchstart));

    let scrollTo = this.scrollPosition + this.distance;

    if (scrollTo < 0) { scrollTo = 0; }
    if (scrollTo > this.maxScrollPos) { scrollTo = this.maxScrollPos; }
    this.style.transition = 'transform 0.2s ease-out'
    this.style.transform = 'translateY('+ (-scrollTo) +'px)';

    e.stopPropagation();
    e.preventDefault();
});
node.addEventListener('touchend', function (e) {
    this.timetouchend = e.timeStamp;

    let speed = Math.pow(this.acc, 2);
    if (speed > 1.6) {
        speed = 1.6;
    }
    this.offset = speed * this.distance;

    this.touchTime = this.timetouchend - this.timetouchmove;

    if (this.acc < 0.1) {
console.log('FIRE!');
        this.origEvent.noTouchScroll = 1;

        let data = this.origEvent;
        let ua = 'raspi';
        let touchEvent = document.createEvent('TouchEvent');

        if (touchEvent && touchEvent.initTouchEvent) {
            if (touchEvent.initTouchEvent.length == 0 && ua !== "iOS") { //chrome
                touchEvent.initTouchEvent(data.touches, data.targetTouches, data.changedTouches, data.type, data.view, data.screenX, data.screenY, data.clientX, data.clientY);
            } else if (touchEvent.initTouchEvent.length == 12) { //firefox
                touchEvent.initTouchEvent(data.type, data.bubbles, data.cancelable, data.view, data.detail, data.ctrlKey, data.altKey, data.shiftKey, data.metaKey, data.touches, data.targetTouches, data.changedTouches);
            } else { //iOS length = 18
                touchEvent.initTouchEvent(data.type, data.bubbles, data.cancelable, data.view, data.detail, data.screenX, data.screenY, data.pageX, data.pageY, data.ctrlKey, data.altKey, data.shiftKey, data.metaKey, data.touches, data.targetTouches, data.changedTouches, data.scale, data.rotation);
            }
        }
alert(touchEvent.defaultPrevented);
        this.dispatchEvent(touchEvent);
        return;
    }

    if (this.touchTime < 100) {
console.log(Math.pow(this.acc, 2));
console.log('Offset: ' + this.offset);
console.log('Distance: ' + this.distance);
        let scrollTo = this.scrollPosition  + this.distance + this.offset;
        if (scrollTo < 0) { scrollTo = 0; }
        if (scrollTo > this.maxScrollPos) { scrollTo = this.maxScrollPos; }
        this.style.transition = 'transform 1.5s ease-out'
        this.style.transform = 'translateY('+ (-scrollTo) +'px)';
    }
    e.stopPropagation();
    e.preventDefault();
});
