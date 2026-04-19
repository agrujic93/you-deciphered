(function() {
	'use strict';

	const FILTER_FADE_DURATION = 250;
	const POST_FADE_IN_DURATION = 350;
	const LOAD_MORE_FADE_DURATION = 300;

	function wait(ms) {
		return new Promise(function(resolve) {
			window.setTimeout(resolve, ms);
		});
	}

	function ensureLoader(postsWrp) {
		let loader = postsWrp.querySelector('.ci-posts-loader');

		if (!loader) {
			loader = document.createElement('div');
			loader.className = 'ci-posts-loader';
			loader.setAttribute('aria-hidden', 'true');
			postsWrp.appendChild(loader);
		}

		return loader;
	}

	function getBlockContext(block) {
		const postsWrp = block.querySelector('.posts-wrp');

		if (!postsWrp) {
			return null;
		}

		return {
			block: block,
			blockId: block.id,
			postsWrp: postsWrp,
			loader: ensureLoader(postsWrp),
			loadMoreBtn: block.querySelector('.ci-load-more-btn'),
		};
	}

	function clearHideTimer(loadMoreBtn) {
		if (loadMoreBtn && loadMoreBtn._ciHideTimer) {
			window.clearTimeout(loadMoreBtn._ciHideTimer);
			loadMoreBtn._ciHideTimer = null;
		}
	}

	function hideLoadMoreButton(loadMoreBtn, immediate) {
		if (!loadMoreBtn) {
			return;
		}

		clearHideTimer(loadMoreBtn);
		loadMoreBtn.classList.add('ci-btn-fade-out');

		if (immediate) {
			loadMoreBtn.classList.add('ci-btn-hidden');
			return;
		}

		loadMoreBtn._ciHideTimer = window.setTimeout(function() {
			loadMoreBtn.classList.add('ci-btn-hidden');
			loadMoreBtn._ciHideTimer = null;
		}, LOAD_MORE_FADE_DURATION);
	}

	function showLoadMoreButton(loadMoreBtn) {
		if (!loadMoreBtn) {
			return;
		}

		clearHideTimer(loadMoreBtn);
		loadMoreBtn.classList.remove('ci-btn-hidden');

		window.requestAnimationFrame(function() {
			loadMoreBtn.classList.remove('ci-btn-fade-out');
		});
	}

	function updateLoadMoreButtonVisibility(loadMoreBtn, hasMore, immediate) {
		if (!loadMoreBtn) {
			return;
		}

		loadMoreBtn.dataset.hasMore = hasMore ? '1' : '0';

		if (hasMore) {
			showLoadMoreButton(loadMoreBtn);
		} else {
			hideLoadMoreButton(loadMoreBtn, !!immediate);
		}
	}

	function getRequestConfig(context) {
		const dataset = context.postsWrp.dataset;

		return {
			category: dataset.category || '',
			postsPerPage: dataset.postsPerPage || 10,
			showThumbnail: dataset.showThumbnail || '',
			showExcerpt: dataset.showExcerpt || '',
			showCategories: dataset.showCategories || '',
			showReadMoreLink: dataset.showReadMoreLink || '',
			showDate: dataset.showDate || '',
			showAuthorName: dataset.showAuthorName || '',
		};
	}

	function createRequestData(action, context, overrides) {
		const config = getRequestConfig(context);
		const finalConfig = Object.assign({}, config, overrides || {});
		const data = new FormData();

		data.append('action', action);
		data.append('block_id', context.blockId);
		data.append('paged', finalConfig.paged);
		data.append('category', finalConfig.category);
		data.append('posts_per_page', finalConfig.postsPerPage);
		data.append('show_thumbnail', finalConfig.showThumbnail);
		data.append('show_excerpt', finalConfig.showExcerpt);
		data.append('show_categories', finalConfig.showCategories);
		data.append('show_read_more_link', finalConfig.showReadMoreLink);
		data.append('show_date', finalConfig.showDate);
		data.append('show_author_name', finalConfig.showAuthorName);
		data.append('nonce', ciBlockData.nonce);

		return data;
	}

	function appendPosts(context, sourceContainer) {
		const appendedPosts = [];
		const posts = sourceContainer.querySelectorAll('.single-blog-wrp');
		const grid = context.postsWrp.querySelector('.uk-grid');

		posts.forEach(function(post) {
			if (grid) {
				grid.appendChild(post);
			} else {
				context.postsWrp.appendChild(post);
			}
			appendedPosts.push(post);
		});

		return appendedPosts;
	}

	function setLoadingState(context, isLoading) {
		const postsWrp = context.postsWrp;
		const loadMoreBtn = context.loadMoreBtn;
		const loader = context.loader || ensureLoader(postsWrp);
		context.loader = loader;

		if (isLoading) {
			postsWrp.classList.add('is-loading');
			if (loadMoreBtn) {
				loadMoreBtn.disabled = true;
				if (loadMoreBtn.dataset.hasMore === '1') {
					loadMoreBtn.classList.add('ci-btn-fade-out');
				}
			}
			return;
		}

		postsWrp.classList.remove('is-loading');

		if (loadMoreBtn) {
			loadMoreBtn.disabled = false;
			updateLoadMoreButtonVisibility(loadMoreBtn, loadMoreBtn.dataset.hasMore === '1', true);
		}
	}

	function updateLoadMoreButton(context, hasMore) {
		const loadMoreBtn = context.loadMoreBtn;

		if (!loadMoreBtn) {
			return;
		}

		loadMoreBtn.disabled = false;
		updateLoadMoreButtonVisibility(loadMoreBtn, !!hasMore, false);
	}

	function getPostItems(postsWrp) {
		return Array.from(postsWrp.querySelectorAll('.single-blog-wrp'));
	}

	function fadeOutExistingPosts(postsWrp) {
		const posts = getPostItems(postsWrp);

		posts.forEach(function(post) {
			post.classList.add('ci-post-fade-out');
		});

		return wait(posts.length ? FILTER_FADE_DURATION : 0);
	}

	function restoreExistingPosts(postsWrp) {
		getPostItems(postsWrp).forEach(function(post) {
			post.classList.remove('ci-post-fade-out');
		});
	}

	function fadeInPosts(posts) {
		if (!posts.length) {
			return;
		}

		posts.forEach(function(post) {
			post.classList.add('ci-post-enter');
		});

		window.requestAnimationFrame(function() {
			window.requestAnimationFrame(function() {
				posts.forEach(function(post) {
					post.classList.add('ci-post-enter-active');
				});
			});
		});

		window.setTimeout(function() {
			posts.forEach(function(post) {
				post.classList.remove('ci-post-enter');
				post.classList.remove('ci-post-enter-active');
			});
		}, POST_FADE_IN_DURATION + 50);
	}

	async function fetchBlogPosts(data) {
		const response = await fetch(ciBlockData.ajaxUrl, {
			method: 'POST',
			body: data,
		});

		if (!response.ok) {
			throw new Error('Request failed with status ' + response.status);
		}

		return response.json();
	}

	// Initialize load more and category filters on document ready
	function initBlogBlock() {
		const blogBlocks = document.querySelectorAll('.ci-blog-block');

		blogBlocks.forEach(function(block) {
			if (block.dataset.ciBlogBlockInitialized === 'true') {
				return;
			}

			const context = getBlockContext(block);

			if (!context) {
				return;
			}

			block.dataset.ciBlogBlockInitialized = 'true';
			context.loader = context.loader || ensureLoader(context.postsWrp);

			const loadMoreBtn = context.loadMoreBtn;
			const categoryPills = block.querySelectorAll('.ci-category-pill');

			if (loadMoreBtn) {
				if (!loadMoreBtn.dataset.hasMore) {
					loadMoreBtn.dataset.hasMore = '1';
				}

				updateLoadMoreButtonVisibility(loadMoreBtn, loadMoreBtn.dataset.hasMore === '1', true);

				loadMoreBtn.addEventListener('click', function(e) {
					e.preventDefault();
					handleLoadMore(context);
				});
			}

			if (categoryPills.length > 0) {
				categoryPills.forEach(function(pill) {
					pill.addEventListener('click', function(e) {
						e.preventDefault();
						handleCategoryFilter(context, this);
					});
				});
			}
		});
	}

	async function handleLoadMore(context) {
		const loadMoreBtn = context.loadMoreBtn;
		const postsWrp = context.postsWrp;

		if (!loadMoreBtn || postsWrp.classList.contains('is-loading')) {
			return;
		}

		setLoadingState(context, true);

		// Get current page from data attribute
		let currentPage = parseInt(postsWrp.dataset.page) || 1;
		currentPage++;

		const data = createRequestData('ci_blog_load_more', context, {
			paged: currentPage,
		});

		try {
			const result = await fetchBlogPosts(data);

			if (result.success) {
				const newPosts = document.createElement('div');
				newPosts.innerHTML = result.data.html;
				const appendedPosts = appendPosts(context, newPosts);

				postsWrp.dataset.page = String(currentPage);
				fadeInPosts(appendedPosts);
				updateLoadMoreButton(context, result.data.has_more);
			} else {
				console.error('Error loading posts:', result.data.message);
			}
		} catch (error) {
			console.error('AJAX error:', error);
		} finally {
			setLoadingState(context, false);
		}
	}

	async function handleCategoryFilter(context, categoryPill) {
		const postsWrp = context.postsWrp;
		const categoryId = categoryPill.dataset.category || '';
		const loadMoreBtn = context.loadMoreBtn;

		if (postsWrp.classList.contains('is-loading')) {
			return;
		}

		// Update active pill
		const allPills = context.block.querySelectorAll('.ci-category-pill');
		allPills.forEach(pill => pill.classList.remove('is-active'));
		categoryPill.classList.add('is-active');

		// Set category filter data
		postsWrp.dataset.category = categoryId;
		postsWrp.dataset.page = 0; // Reset to first page

		setLoadingState(context, true);
		const fadeOutPromise = fadeOutExistingPosts(postsWrp);

		const data = createRequestData('ci_blog_category_filter', context, {
			category: categoryId,
			paged: 1,
		});

		try {
			const [result] = await Promise.all([
				fetchBlogPosts(data),
				fadeOutPromise,
			]);

			if (result.success) {
				const filteredPosts = document.createElement('div');
				filteredPosts.innerHTML = result.data.html;

				getPostItems(postsWrp).forEach(function(post) {
					post.remove();
				});

				const insertedPosts = appendPosts(context, filteredPosts);

				postsWrp.dataset.page = '1';
				fadeInPosts(insertedPosts);
				updateLoadMoreButton(context, result.data.has_more);
			} else {
				restoreExistingPosts(postsWrp);
				if (loadMoreBtn) {
					loadMoreBtn.disabled = false;
				}
				console.error('Error filtering posts:', result.data.message);
			}
		} catch (error) {
			restoreExistingPosts(postsWrp);
			if (loadMoreBtn) {
				loadMoreBtn.disabled = false;
			}
			console.error('AJAX error:', error);
		} finally {
			setLoadingState(context, false);
		}
	}

	// Initialize on document ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBlogBlock);
	} else {
		initBlogBlock();
	}

	// Also initialize for dynamically added content
	if (window.MutationObserver) {
		const observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.addedNodes.length) {
					mutation.addedNodes.forEach(function(node) {
						if (node.nodeType === 1 && node.classList && node.classList.contains('ci-blog-block')) {
							initBlogBlock();
						}
					});
				}
			});
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
		});
	}
})();
