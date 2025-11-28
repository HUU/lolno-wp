import Prism from 'prismjs';
import $ from 'jquery';

/*
 * ----------------------------------------------------------------
 * Event Handlers
 * ----------------------------------------------------------------
 */

const scroll = {
	y: null,
	handlers: [],
	init: function () {
		window.addEventListener('scroll', (e) => {
			window.requestAnimationFrame(scroll.run)
		}, false);
	},
	run: function (timestamp, resized) {
		const scrollTop = window.pageYOffset;
		if (resized || scrollTop !== scroll.y) {
			scroll.y = scrollTop;

			if (scroll.resetTo > -1) { // hack to fix scroll jump during popstate (back and forward buttons)
				scroll.resumeTo = scrollTop;
				scroll.y = scroll.resetTo;
				window.scrollTo(0, scroll.resetTo);
				scroll.resetTo = -1;
				return;
			}

			for (let i = 0, len = scroll.handlers.length; i < len; i++) if (scroll.handlers[i]) {
				scroll.handlers[i](scrollTop);
			}
		}
	},
	add: function (handler) {
		this.handlers.push(handler);
		handler(window.pageYOffset);
	},
	remove: function (handler) {
		const index = this.handlers.indexOf(handler);
		if (index !== -1) this.handlers.splice(index, 1);
	}
}

scroll.init();


const scrollbarWidth = (() => {
	if (typeof document === 'undefined') return 0;

	const box = document.createElement('div'), boxStyle = box.style;
	boxStyle.position = 'absolute';
	boxStyle.top = boxStyle.left = '-9999px';
	boxStyle.width = boxStyle.height = '100px';
	boxStyle.overflow = 'scroll';

	document.body.appendChild(box);
	const width = box.offsetWidth - box.clientWidth;
	document.body.removeChild(box);
	return width;
})();


const resize = {
	handlers: [],
	init: function () {
		this.viewportWidth = document.documentElement.clientWidth + scrollbarWidth;
		window.addEventListener('orientationchange', this.run, false);
		window.addEventListener('resize', this.debounce(this.run, 40), false);
		window.addEventListener('load', this.run, false);
	},
	run: function () {
		resize.viewportWidth = document.documentElement.clientWidth + scrollbarWidth;
		for (let i = 0, len = resize.handlers.length; i < len; i++) if (resize.handlers[i]) {
			resize.handlers[i](resize.viewportWidth);
		}
		scroll.run(0, true);
	},
	debounce: function (fn, time) {
		let timeout;
		return function (...args) {
			const functionCall = () => fn.apply(this, args);
			clearTimeout(timeout);
			timeout = setTimeout(functionCall, time);
		}
	},
	add: function (handler) {
		this.handlers.push(handler);
		handler(this.viewportWidth);
	},
	remove: function (handler) {
		const index = this.handlers.indexOf(handler);
		if (index !== -1) this.handlers.splice(index, 1);
	}
}

resize.init();

/*
 * ----------------------------------------------------------------
 * Utils
 * ----------------------------------------------------------------
 */

function animate (draw, duration, timing) {
	const start = performance.now();
	const animate = (time) => {
		if (animate.stop === true) return;
		let timeFraction = (time - start) / duration;
			timeFraction = timeFraction < 0 ? 0 : (timeFraction > 1 ? 1 : timeFraction);

		draw(timing ? timing(timeFraction) : timeFraction);
		if (timeFraction < 1) requestAnimationFrame(animate);
	}
	requestAnimationFrame(animate);
	return animate;
}

const prefix = (node, style, value) => {
	node.style['Webkit' + style] = value;
	node.style['Moz' + style] = value;
	node.style['ms' + style] = value;
	node.style['o' + style] = value;
	node.style[style] = value;
}

const wrapNode = (node, className) => {
	const wrap = document.createElement('div');
	if (className) wrap.classList.add(className);
	node.parentNode.insertBefore(wrap, node);
	wrap.appendChild(node);
	return wrap;
}

const isObject = (obj) => obj && typeof obj === 'object';
const merge = (target, source) => {
	if (!isObject(target) || !isObject(source)) return source;

	Object.keys(source).forEach(key => {
		if (Array.isArray(target[key]) && Array.isArray(source[key])) {
			target[key] = target[key].concat(source[key]);
		} else if (isObject(target[key]) && isObject(source[key])) {
			target[key] = merge(Object.assign({}, target[key]), source[key]);
		} else {
			target[key] = source[key];
		}
	});
	return target;
}

const enteredViewport = (element, callback, lazyness) => {
	let top, bottom;
	const onresize = () => {
		({ top, bottom } = element.getBoundingClientRect());
		top    += window.pageYOffset - window.innerHeight * ((lazyness || 0) + 1);
		bottom += window.pageYOffset + window.innerHeight *  (lazyness || 0);
	}
	const onscroll = (scrollTop) => {
		if (top > scrollTop || bottom < scrollTop) return;
		destroy(); callback();
	}
	const destroy = () => {
		scroll.remove(onscroll);
		resize.remove(onresize);
	}
	resize.add(onresize);
	scroll.add(onscroll);

	return { destroy: destroy };
}

const parallax = (element, speed, options) => {
	options = options || {};
	let top, bottom, height, coeff = speed, referenceEl = options.bgWrap ? options.bgWrap : element;
	const onscroll = (scrollTop) => {
		if (top > scrollTop + height || bottom < scrollTop) return;

		element.y = coeff * (scrollTop - top);
		if (element.bg) prefix(element.bg, 'transform', 'translateY(' + element.y + 'px) translateZ(0px)');
	}
	const onresize = () => {
		({ top, bottom } = referenceEl.getBoundingClientRect());
		top    += window.pageYOffset - (element.yOffset || 0); // yOffset fixes the ajax fade-in animation
		bottom += window.pageYOffset - (element.yOffset || 0);
		height  = window.innerHeight;

		// TODO: If you find better way to fix parallax on mobile please contact me
		coeff = speed * Math.min(1, window.innerWidth / window.innerHeight);

		const oldHeight = bottom - top;
		const newHeight = (oldHeight + (height - oldHeight) * coeff);
		if (element.bg) element.bg.style.height = newHeight + 'px';
		if (options.resizeCallback) options.resizeCallback(newHeight);
	}
	const destroy = () => {
		scroll.remove(onscroll);
		resize.remove(onresize);
		if (element.bg) {
			element.bg.style.removeProperty('height');
			prefix(element.bg, 'transform', '');
		}
	}
	resize.add(onresize);
	scroll.add(onscroll);

	return { destroy: destroy };
}

const imageLoaded = (element, callback) => {
	let image = new Image(), source = element.src;
	if (element.tagName != 'IMG') {
		source = element.getAttribute('data-src');
		if (!source) source = element.style.backgroundImage.slice(4, -1).replace(/['"]/g, "");
	}
	image.src = source;

	if (image.complete && image.naturalHeight !== 0) {
		callback(element, { error: false, wasComplete: true });
	} else {
		image.onload  = () => callback(element, { error: false });
		image.onerror = () => callback(element, { error: true  });
	}
	return { destroy: () => { image = image.onload = image.onerror = null; } }
}

/*
 * ----------------------------------------------------------------
 * Prev/Next Post Section
 * ----------------------------------------------------------------
 */

const siblingPosts = {
	posts: [],
	init: function () {
		this.posts = document.querySelectorAll('.sibling-posts a');
		if (this.absent = !this.posts.length) return;

		for (let i = 0, len = this.posts.length; i < len; i++) {
			const post = this.posts[i];
			const bg = post.querySelector('.background-image .lazyload');
			if (bg) post.inView = enteredViewport(post, () => post.imageLoaded = imageLoaded(bg, () => {
				bg.style.backgroundImage = `url(${ bg.getAttribute('data-src') })`;
				bg.parentNode.classList.add('visible');
			}) );
		}
	},
	destroy: function () {
		for (let i = 0, len = this.posts.length; i < len; i++) {
			const post = this.posts[i];
			if (post.inView) post.inView.destroy();
			if (post.imageLoaded) post.imageLoaded.destroy();
		}
		this.posts = [];
	}
}


/*
 * ----------------------------------------------------------------
 * Syntax Highlighting
 * ----------------------------------------------------------------
 */

const highlight = {
	init: function () {
		const container = document.querySelector('.post-full-content');
		if (container) Prism.highlightAllUnder(container);
	}
}

/*
 * ----------------------------------------------------------------
 * Stupid Random Taglines
 * ----------------------------------------------------------------
 */

const taglines = {
	init: function () {
		const container = document.querySelector('.site-description');
		if (container && window.taglines) {
			container.innerHTML = window.taglines[Math.floor(Math.random() * window.taglines.length)];
		}
	}
}

/*
 * ----------------------------------------------------------------
 * Page Loading
 * ----------------------------------------------------------------
 */
$(document).ready(function() {
    siblingPosts.init();
	highlight.init();
	taglines.init();
});