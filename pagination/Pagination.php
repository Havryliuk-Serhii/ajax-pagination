<?php
/**
 * 
 */
class Pagination
{
	
	function __construct(){
		add_shortcode('pagination_test',[$this,'view']);
		add_action('wp_footer', [$this, 'scripts']);
		add_action('wp_ajax_paginatin_test', [$this, 'ajax']);
		add_action('wp_ajax_nopriv_paginatin_test', [$this, 'ajax']);
	}

	public function scripts(){
		?>
		<script>
			var  a_url={
				admin_url:'<?php echo admin_url('admin-ajax.php'); ?>',
				nonce:'<?php echo wp_create_nonce('pagination-text-nonce'); ?>',

			};
		</script>
		<script>
			const body=document.querySelector('body');

			body.addEventListener('click',function(e){
				if(e.target.classList.contains('page-numbers')){
					return;
				}

				e.preventDefault();
				const link=e.target,
					formData= new FormData(),
					wrapper=link.parentElement;

				let page = +link.textContent;
				
				if (link.classList.contains('prev')){
					page=parseInt(wrapper.querySelector('current').textContent)-1;
				}
					
				if (link.classList.contains('next')){
					page=parseInt(wrapper.querySelector('current').textContent)+1;
				}	
					
				formData.append('action','pagination_test');
				formData.append('_ajax_nonce', a_url.nonce);
				formData.append('page', page);

				fetch(a_url.admin_url,{
					method:'POST',
					body:formData,
				})
					.then((response)=>response.json())
					.then((response)=>{
						wrapper.innerHTML=response.data;
					});
			});
			
		</script>

		<?php

	}

	public function view(){

		$current_page=max(1, get_query_var('paged'));
		return '<div class="relative-posts">'. $this->content($current_page).'</div>';
			
	}

	public function ajax(){
		check_ajax_referer('pagination-text-nonce');
		$page=filter_input(INPUT_POST,'page', FILTER_VALIDATE_INT);
		$page=max(1,$page);
		wp_send_json_success(
			$this->content($page)
		);
		
	}

	private function content($page){

		$query= new WP_Query(
			[
				'post_type'=>'post',
				'posts_per_page'=>1,
				'paged'=> $page,
			]
		);

		ob_start();
		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				the_permalink();
			}
			wp_reset_postdata();
		}
		echo '<br>';

		echo paginate_links(
			[
				'current'=> $page,
				'total'=>$query->max_num_pages,
			]
		);
		return ob_get_clean();
	}
}
new Pagination();