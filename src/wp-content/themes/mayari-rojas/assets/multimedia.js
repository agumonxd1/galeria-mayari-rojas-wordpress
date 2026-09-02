document.addEventListener('DOMContentLoaded',()=>{
	const filters=document.querySelector('.gmr-media-filters[data-dynamic]');
	if(filters){
		const cards=[...document.querySelectorAll('.gmr-media-card')];
		const grid=document.querySelector('[data-media-grid]');
		const count=document.querySelector('[data-media-count]');
		const title=document.querySelector('[data-media-title]');
		const empty=document.querySelector('.gmr-media-no-results');
		const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
		const apply=(filter,label,url,push=false)=>{
			filters.querySelectorAll('[data-filter]').forEach(link=>{const selected=link.dataset.filter===filter;link.classList.toggle('is-current',selected);if(selected)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current')});
			const visible=cards.filter(card=>filter==='all'||card.dataset.mediaTopics.split(' ').includes(filter));
			cards.forEach(card=>{const show=visible.includes(card);card.classList.remove('is-entering');if(show){card.hidden=false;if(!reduced)requestAnimationFrame(()=>card.classList.add('is-entering'))}else card.hidden=true});
			if(grid)grid.hidden=!visible.length;if(empty)empty.hidden=!!visible.length;if(count)count.textContent=String(visible.length);if(title)title.textContent=label;if(push&&url)history.pushState({mediaFilter:filter},'',url);
		};
		filters.addEventListener('click',event=>{const link=event.target.closest('[data-filter]');if(!link)return;event.preventDefault();apply(link.dataset.filter,link.dataset.label,link.href,true)});
		addEventListener('popstate',()=>{const link=[...filters.querySelectorAll('[data-filter]')].find(item=>item.href===location.href)||filters.querySelector('[data-filter="all"]');apply(link.dataset.filter,link.dataset.label,link.href,false)});
		const initial=filters.querySelector(`[data-filter="${filters.dataset.active}"]`)||filters.querySelector('[data-filter="all"]');apply(initial.dataset.filter,initial.dataset.label,initial.href,false);
	}

	const gallery=document.querySelector('[data-lightbox-gallery]');
	const viewer=document.querySelector('[data-lightbox]');
	if(!gallery||!viewer)return;
	const items=[...gallery.querySelectorAll('[data-lightbox-item]')];
	const image=viewer.querySelector('[data-lightbox-image]');
	const caption=viewer.querySelector('[data-lightbox-caption]');
	const counter=viewer.querySelector('[data-lightbox-count]');
	const closeButton=viewer.querySelector('[data-lightbox-close]');
	let current=0,lastFocus=null,touchStartX=0;
	const preload=index=>{const next=items[(index+items.length)%items.length];if(next){const loader=new Image();loader.src=next.dataset.full}};
	const show=(index,animate=true)=>{
		current=(index+items.length)%items.length;
		const item=items[current];
		if(animate)viewer.classList.add('is-changing');
		const update=()=>{image.src=item.dataset.full;image.alt=item.dataset.alt||'';caption.textContent=item.dataset.alt||'';counter.textContent=`${current+1} / ${items.length}`;requestAnimationFrame(()=>viewer.classList.remove('is-changing'));preload(current+1);preload(current-1)};
		if(animate)setTimeout(update,120);else update();
	};
	const open=index=>{lastFocus=document.activeElement;viewer.hidden=false;document.body.classList.add('gmr-lightbox-open');show(index,false);closeButton.focus()};
	const close=()=>{viewer.hidden=true;document.body.classList.remove('gmr-lightbox-open');image.src='';if(lastFocus)lastFocus.focus()};
	gallery.addEventListener('click',event=>{const item=event.target.closest('[data-lightbox-item]');if(item)open(items.indexOf(item))});
	viewer.querySelector('[data-lightbox-previous]').addEventListener('click',()=>show(current-1));
	viewer.querySelector('[data-lightbox-next]').addEventListener('click',()=>show(current+1));
	closeButton.addEventListener('click',close);
	viewer.addEventListener('click',event=>{if(event.target===viewer)close()});
	viewer.addEventListener('touchstart',event=>{touchStartX=event.changedTouches[0].clientX},{passive:true});
	viewer.addEventListener('touchend',event=>{const distance=event.changedTouches[0].clientX-touchStartX;if(Math.abs(distance)>55)show(current+(distance<0?1:-1))},{passive:true});
	document.addEventListener('keydown',event=>{if(viewer.hidden)return;if(event.key==='Escape')close();if(event.key==='ArrowLeft')show(current-1);if(event.key==='ArrowRight')show(current+1);if(event.key==='Tab'){const focusable=[...viewer.querySelectorAll('button')];const first=focusable[0],last=focusable[focusable.length-1];if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus()}else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus()}}});
});
