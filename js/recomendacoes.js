// recomendacoes.js - (corrigido) Recomendações: filtro, animações, lidas e contador sem duplicar

(function () {
  // Evita inicializar duas vezes (mesmo que o script seja carregado 2x)
  if (window.__recsInit) return;
  window.__recsInit = true;

  document.addEventListener('DOMContentLoaded', function () {
    initFilterButtons();
    initAnimations();

    restoreReadState();
    initMarkAsRead();

    updateUnreadCount();
  });

  // -----------------------------
  // Filtros
  // -----------------------------
  function initFilterButtons() {
    const section = document.querySelector('.recommendations-section');
    if (!section || !document.querySelector('.recommendation-card')) return;

    const header = section.querySelector('.section-header');
    if (!header) return;

    // Evita criar filtro 2x
    if (header.querySelector('.filter-container')) return;

    const filterContainer = document.createElement('div');
    filterContainer.className = 'filter-container';
    filterContainer.innerHTML = `
      <div class="filter-buttons">
        <button class="filter-btn active" data-filter="all">Todas</button>
        <button class="filter-btn" data-filter="alerta">Alertas</button>
        <button class="filter-btn" data-filter="aviso">Avisos</button>
        <button class="filter-btn" data-filter="sucesso">Sucessos</button>
        <button class="filter-btn" data-filter="dica">Dicas</button>
      </div>
    `;
    header.appendChild(filterContainer);

    const filterButtons = filterContainer.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
      button.addEventListener('click', function () {
        filterButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        filterRecommendations(this.dataset.filter);
      });
    });
  }

  function filterRecommendations(filter) {
    const cards = document.querySelectorAll('.recommendation-card');
    let visibleCount = 0;

    cards.forEach(card => {
      if (filter === 'all' || card.classList.contains(filter)) {
        card.style.display = 'block';
        visibleCount++;
        card.style.animation = 'fadeIn 0.5s ease';
      } else {
        card.style.display = 'none';
      }
    });

    updateFilterCount(visibleCount);
  }

  function updateFilterCount(count) {
    const subtitle = document.querySelector('.recommendations-section .section-subtitle');
    const totalCards = document.querySelectorAll('.recommendations-section .recommendation-card').length;

    if (!subtitle) return;

    if (count === totalCards) subtitle.textContent = `${count} sugestões personalizadas`;
    else subtitle.textContent = `${count} de ${totalCards} sugestões (filtradas)`;
  }

  // -----------------------------
  // Animações
  // -----------------------------
  function initAnimations() {
    const cards = document.querySelectorAll('.recommendation-card, .tip-card');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';

      setTimeout(() => {
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, 100 + (index * 50));
    });

    const tipCards = document.querySelectorAll('.tip-card');
    tipCards.forEach(card => {
      card.addEventListener('mouseenter', function () {
        this.style.transform = 'translateY(-5px)';
      });
      card.addEventListener('mouseleave', function () {
        this.style.transform = 'translateY(0)';
      });
    });
  }

  // -----------------------------
  // Lidas (localStorage)
  // -----------------------------
  function storageKey() {
    return 'read_recs_v1';
  }

  function getReadSet() {
    return new Set(JSON.parse(localStorage.getItem(storageKey()) || '[]'));
  }

  function saveReadSet(set) {
    localStorage.setItem(storageKey(), JSON.stringify([...set]));
  }

  function restoreReadState() {
    const read = getReadSet();
    document.querySelectorAll('.recommendation-card').forEach(card => {
      if (read.has(card.dataset.recId)) card.classList.add('read');
    });
  }

  function initMarkAsRead() {
    const cards = document.querySelectorAll('.recommendation-card');

    cards.forEach(card => {
      card.addEventListener('click', function () {
        if (this.classList.contains('read')) return;

        this.classList.add('read');

        const read = getReadSet();
        read.add(this.dataset.recId);
        saveReadSet(read);

        this.style.opacity = '0.9';

        const footer = this.querySelector('.card-footer');
        if (footer && !footer.querySelector('.read-indicator')) {
          const indicator = document.createElement('span');
          indicator.className = 'read-indicator';
          indicator.innerHTML = '<i class="fas fa-check"></i> Lido';
          indicator.style.cssText = `
            font-size: 0.75rem;
            color: #38a169;
            display: flex;
            align-items: center;
            gap: 5px;
          `;
          footer.appendChild(indicator);
        }

        updateUnreadCount();
      });
    });
  }

  // -----------------------------
  // Contador SEM duplicar
  // -----------------------------
  function updateUnreadCount() {
    const unreadCards = document.querySelectorAll('.recommendations-section .recommendation-card:not(.read)');
    const count = unreadCards.length;

    const title = document.querySelector('.recommendations-section .section-header h3');
    if (!title) return;

    // Guarda uma vez o texto base LIMPO (sem "(X novas)" e sem números)
    if (!title.dataset.baseText) {
      const base = title.textContent
        .replace(/\(\s*\d+\s+novas?\s*\)/gi, '')
        .replace(/\s*\d+\s*$/, '')
        .trim();
      title.dataset.baseText = base;
    }

    // Mantém o ícone e reescreve o conteúdo SEM badge separado (evita "4 3 2 1")
    const icon = title.querySelector('i') ? title.querySelector('i').cloneNode(true) : null;
    title.innerHTML = '';
    if (icon) {
      title.appendChild(icon);
      title.appendChild(document.createTextNode(' '));
    }

    const txt = (count > 0)
      ? `${title.dataset.baseText} (${count} novas)`
      : title.dataset.baseText;

    title.appendChild(document.createTextNode(txt));
  }

  // -----------------------------
  // CSS dinâmico (igual ao teu)
  // -----------------------------
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .filter-container { margin-top: 10px; }
    .filter-buttons { display: flex; gap: 8px; flex-wrap: wrap; }

    .filter-btn {
      padding: 6px 12px;
      background: #e2e8f0;
      border: none;
      border-radius: 16px;
      color: #4a5568;
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .filter-btn.active { background: #805ad5; color: white; }
    .filter-btn:hover:not(.active) { background: #cbd5e0; }

    .recommendation-card.read { opacity: 0.9; }
    .recommendation-card.read .recommendation-icon { opacity: 0.8; }
  `;
  document.head.appendChild(style);
})();
