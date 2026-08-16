document.addEventListener('DOMContentLoaded',()=>{
	const filters=document.querySelector('.gmr-media-filters[data-dynamic]');
	if(!filters)return;
	const cards=[...document.querySelectorAll('.gmr-media-card')];
	const grid=document.querySelector('[data-media-grid]');
	const count=document.querySelector('[data-media-count]');
	const title=document.querySelector('[data-media-title]');
	const empty=document.querySelector('.gmr-media-no-results');
	const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
	const apply=(filter,label,url,push=false)=>{
		filters.querySelectorAll('[data-filter]').forEach(link=>{const current=link.dataset.filter===filter;link.classList.toggle('is-current',current);if(current)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current')});
		const visible=cards.filter(card=>filter==='all'||card.dataset.mediaTopics.split(' ').includes(filter));
		cards.forEach(card=>{const show=visible.includes(card);card.classList.remove('is-entering');if(show){card.hidden=false;if(!reduced)requestAnimationFrame(()=>card.classList.add('is-entering'))}else card.hidden=true});
		if(grid)grid.hidden=!visible.length;if(empty)empty.hidden=!!visible.length;if(count)count.textContent=String(visible.length);if(title)title.textContent=label;if(push&&url)history.pushState({mediaFilter:filter},'',url);
	};
	filters.addEventListener('click',event=>{const link=event.target.closest('[data-filter]');if(!link)return;event.preventDefault();apply(link.dataset.filter,link.dataset.label,link.href,true)});
	addEventListener('popstate',()=>{const link=[...filters.querySelectorAll('[data-filter]')].find(item=>item.href===location.href)||filters.querySelector('[data-filter="all"]');apply(link.dataset.filter,link.dataset.label,link.href,false)});
	const initial=filters.querySelector(`[data-filter="${filters.dataset.active}"]`)||filters.querySelector('[data-filter="all"]');apply(initial.dataset.filter,initial.dataset.label,initial.href,false);
});
