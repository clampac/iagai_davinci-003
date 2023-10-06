**Passo 1:** Criar a tabela no banco de dados

CREATE TABLE `clapac_iagai_img_file_name` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`title_id` int(11) NOT NULL,
`originalName` varchar(255) NOT NULL,
`suggestedName` varchar(255) NOT NULL,
`trash` tinyint(1) NOT NULL DEFAULT 0,
`updated` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

**Passo 2:** Criar a função 'meusPosts' para percorrer um array de post_ids e retornar um array de dicionários.

function meusPosts( $post_ids ) {
$posts = array();
foreach ( $post_ids as $post_id ) {
$post = array(
'originalName' => get_the_title( $post_id ),
'suggestedName' => sugerirNome( get_the_title( $post_id ) ),
'post_id' => $post_id
);
array_push( $posts, $post );
}
return $posts;
}

**Passo 3:** Criar a opção de submenu no painel de administração do wordpress

// Adicionar a opção de submenu
add_action( 'admin_menu', 'create_submenu_meus_posts' );

function create_submenu_meus_posts() {
add_submenu_page(
'edit.php',
'Meus Posts',
'Meus Posts',
'manage_options',
'meus_posts',
'meus_posts_page'
);
}

// Criar a página
function meus_posts_page() {
global $wpdb;
// Código para exibir a tabela com os valores salvos
}

**Passo 4:** Escrever a lógica do programa

// Função para salvar os dados
function salvarDados( $posts ) {
global $wpdb;
foreach ( $posts as $post ) {
$wpdb->insert(
'clapac_iagai_img_file_name',
array(
'post_id'      => $post['post_id'],
'originalName' => $post['originalName'],
'suggestedName' => $post['suggestedName'],
),
array(
'%d',
'%s',
'%s'
)
);
}
}

// Função para atualizar os dados
function updateImages( $ids ) {
global $wpdb;
foreach ( $ids as $id ) {
$wpdb->update(
'clapac_iagai_img_file_name',
array(
'updated' => 1
),
array(
'id' => $id
),
array(
'%d',
'%d'
)
);
}
}

// Função para deletar os dados
function deleteImages( $ids ) {
global $wpdb;
foreach ( $ids as $id ) {
$wpdb->delete(
'clapac_iagai_img_file_name',
array(
'id' => $id
)
);
}
}

**Passo 5:** Usar bootstrap para o layout

<div class="container">
	<h1>Meus posts</h1>
	<form method="post">
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Nome original</th>
				<th>Nome sugerido</th>
				<th>Atualizar</th>
				<th>Deletar</th>
			</tr>
			</thead>
			<tbody>
                