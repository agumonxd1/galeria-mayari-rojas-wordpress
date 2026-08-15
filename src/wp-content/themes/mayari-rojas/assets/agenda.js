document.addEventListener('DOMContentLoaded',()=>{
	const filters=document.querySelector('.gmr-agenda-filters[data-dynamic]');
	if(!filters)return;
	const cards=[...document.querySelectorAll('.gmr-event-card')];
	const sections=[...document.querySelectorAll('[data-event-section]')];
	const count=document.querySelector('[data-agenda-count]');
	const title=document.querySelector('[data-agenda-title]');
	const empty=document.querySelector('.gmr-agenda-no-results');
	const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
	const apply=(filter,label,url,push=false)=>{
		filters.setAttribute('aria-busy','true');
		filters.querySelectorAll('[data-filter]').forEach(link=>{const current=link.dataset.filter===filter;link.classList.toggle('is-current',current);if(current)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current')});
		const visible=cards.filter(card=>filter==='all'||card.dataset.eventTypes.split(' ').includes(filter));
		cards.forEach(card=>{const show=visible.includes(card);card.classList.remove('gmr-event-card--featured','is-entering');if(show){card.hidden=false;if(!reduced)requestAnimationFrame(()=>card.classList.add('is-entering'))}else if(reduced)card.hidden=true;else{card.classList.add('is-leaving');setTimeout(()=>{card.hidden=true;card.classList.remove('is-leaving')},180)}});
		visible[0]?.classList.add('gmr-event-card--featured');
		sections.forEach(section=>{const hasVisible=visible.some(card=>section.contains(card));section.hidden=!hasVisible});
		if(empty)empty.hidden=visible.length>0;
		if(count)count.textContent=String(visible.length);
		if(title)title.textContent=label;
		if(push&&url)history.pushState({agendaFilter:filter},'',url);
		setTimeout(()=>filters.removeAttribute('aria-busy'),reduced?0:220);
	};
	filters.addEventListener('click',event=>{const link=event.target.closest('[data-filter]');if(!link)return;event.preventDefault();apply(link.dataset.filter,link.dataset.label,link.href,true)});
	addEventListener('popstate',()=>{const link=[...filters.querySelectorAll('[data-filter]')].find(item=>item.href===location.href)||filters.querySelector('[data-filter="all"]');apply(link.dataset.filter,link.dataset.label,link.href,false)});
	const initial=filters.querySelector(`[data-filter="${filters.dataset.active}"]`)||filters.querySelector('[data-filter="all"]');
	apply(initial.dataset.filter,initial.dataset.label,initial.href,false);
});
