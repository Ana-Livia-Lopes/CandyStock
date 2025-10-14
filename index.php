<?php
include 'php/features.php';

if (!Usuario::hasSessao()) {
  header("Location: login.php");
}

$produtos = PRODUTO::pesquisar(
  isset($_GET['pesquisa']) ? $_GET['pesquisa'] : null
);

$countProdutosEmFalta = 0;
$countProdutosSemFalta = 0;

foreach ($produtos as $produto) {
  if ($produto->hasEstoqueBaixo()) {
    $countProdutosEmFalta++;
  } else {
    $countProdutosSemFalta++;
  }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Controle de Estoque</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rethink+Sans:ital,wght@0,400..800;1,400..800&family=Space+Grotesk:wght@300..700&display=swap"
    rel="stylesheet">
</head>

<body>
  <div class="cabecalho">
    <div class="menu">
      <img src="./img/logo-candy-senfundo.png" alt="logo" class="logo">
      <ul>
        <li><a href="index.php">Produtos</a></li>
        <li><a href="relatorios.php">Relatórios</a></li>
        <li><a href="perfil.php">Perfil</a></li>
        <li><a href="sair.php">Sair</a></li>
      </ul>
    </div>
    <div class="texto-cabecalho">
      <h1>Seu estoque <span>organizado</span>, <br>seu negócio ainda mais <b>doce</b>
    </div>
  </div>

  <!-- info -->
  <div class="resumo">
    <div class="caixa-resumo">
      <p><i class="fa-solid iconresumo fa-box-archive"></i> <?php
      $quantidade = count($produtos);
      if ($quantidade == 1) {
        echo "$quantidade Produto";
      } else {
        echo "$quantidade Produtos";
      }
    ?></p>
    </div>
    <div class="caixa-resumo">
      <p><i class="fa-solid iconresumo fa-circle-check"></i> <?= $countProdutosSemFalta ?> Sem falta</p>
    </div>
    <div class="caixa-resumo">
      <p><i class="fa-solid iconresumo fa-circle-xmark"></i> <?= $countProdutosEmFalta ?> Em falta</p>
    </div>
  </div>

  <!-- Pesquisa -->
  <form class="pesquisa">
    <input type="text" placeholder="Ex.: Trufa de chocolate" name="pesquisa">
  </form>

  <!-- Produtos -->
  <div class="produtos">
  <?php
    if (isset($_GET['pesquisa'])) {
      echo "<h1>Pesquisando por: {$_GET['pesquisa']}</h1>";
    }
  ?>
    <div class="button">
      Adicionar produto
    </div>
    <?php
      foreach ($produtos as $produto) {
        /**
         * @var Produto $produto
         */
        $produto;
        echo '<div class="caixa-produto">
      <div id="caixa-img">
        <img src="' . $produto->getImagem()->getCaminho() .'" alt="' . $produto->getNome() . '">
      </div>
      <div class="info">
        <div class="comment">
          <div>
            <h2>' . $produto->getNome() . '</h2>
          </div>
          <div class="comentarios" id-produto=' . $produto->id . '>
            <i class="fa-solid fa-comments"></i>
          </div>
        </div>
        <div id="itens">
          <!-- <p>Peso: 100g</p> -->
          <p>Quantidade: ' . $produto->getEstoque() . '</p>
          <p>Preço: R$ ' . $produto->getPreco() . '</p>
        </div>
        <div class="botoes">
          <i class="fa-solid fa-square-xmark" id-produto=' . $produto->id . '></i>
          <i class="fa-solid fa-square-pen" id-produto=' . $produto->id . '></i>
          <i class="fa-solid fa-square-plus" id-produto=' . $produto->id . '></i>
          <i class="fa-solid fa-square-minus" id-produto=' . $produto->id . '></i>
        </div>
        <p class="descricao">'. $produto->getDescricao() .'</p>
      </div>
    </div>';
      }
    ?>
    <!-- <div class="caixa-produto">
      <div id="caixa-img">
        <img src="./img/trufaC.png" alt="Trufa de chocolate">
      </div>
      <div class="info">
        <div class="comment">
          <div>
            <h2>Trufa da Chocolate</h2>
          </div>
          <div class="comentarios">
            <i class="fa-solid fa-comments"></i>
          </div>
        </div>
        <div id="itens">
          <p>Peso: 100g</p>
          <p>Quantidade: 1465</p>
          <p>Preço: R$ 5,90</p>
        </div>
        <div class="botoes">
          <i class="fa-solid fa-square-xmark"></i>
          <i class="fa-solid fa-square-pen"></i>
        </div>
        <p class="descricao">Produto à base de chocolate, com recheio cremoso interno e cobertura de chocolate ao leite.
          Textura macia no interior e casca firme externa. </p>
      </div>
    </div>

    <div class="caixa-produto">
      <div id="caixa-img">

        <img src="./img/fini.png" alt="Fini">
      </div>
      <div class="info">
        <div class="comment">
          <div>
            <h2>Pacote FINI Dentaduras</h2>
          </div>
          <div class="comentarios">
            <i class="fa-solid fa-comments"></i>
          </div>
        </div>
        <div id="itens">
          <p>Peso: 90g</p>
          <p>Quantidade: 654</p>
          <p>Preço: R$ 6,90</p>
        </div>
        <div class="botoes">
          <i class="fa-solid fa-square-xmark"></i>
          <i class="fa-solid fa-square-pen"></i>
        </div>
        <p class="descricao">Balas de gelatina em formato de dentadura. Textura macia e elástica, com sabor frutado.
          Coloridas artificialmente. Produto industrializado.</p>
      </div>
    </div>

    <div class="caixa-produto">
      <div id="caixa-img">

        <img src="./img/bis.png" alt="BIS">
      </div>
      <div class="info">
        <div class="comment">
          <div>
            <h2>BIS Chocolate Lacta</h2>
          </div>
          <div class="comentarios">
            <i class="fa-solid fa-comments"></i>
          </div>
        </div>
        <div id="itens">
          <p>Peso: 126g</p>
          <p>Quantidade: 1882</p>
          <p>Preço: R$ 8,99</p>
        </div>
        <div class="botoes">

          <i class="fa-solid fa-square-xmark"></i>
          <i class="fa-solid fa-square-pen"></i>
        </div>
        <p class="descricao">Wafer em camadas, coberto com chocolate ao leite. Textura crocante e leve. Produto
          industrializado, embalado em unidades pequenas.</p>
      </div>
    </div>

    <div class="caixa-produto">
      <div id="caixa-img">

        <img src="./img/7belo.png" alt="7Belo">
      </div>
      <div class="info">
        <div class="comment">
          <div>
            <h2>Pacote Bala 7Belo</h2>
          </div>
          <div class="comentarios">
            <i class="fa-solid fa-comments"></i>
          </div>
        </div>
        <div id="itens">
          <p>Peso: 600g</p>
          <p>Quantidade: 1542</p>
          <p>Preço: R$ 11,90</p>
        </div>
        <div class="botoes">
          <i class="fa-solid fa-square-xmark"></i>
          <i class="fa-solid fa-square-pen"></i>
        </div>
        <p class="descricao">Bala sabor framboesa, de consistência firme e coloração rosada. Possui aroma característico e sabor artificial de fruta. Embalada individualmente.</p>
      </div>
    </div>

    <div class="caixa-produto">
      <div id="caixa-img">

        <img src="./img/ourobranco.png" alt="Ouro Branco">
      </div>
      <div class="info">
        <div class="comment">
          <div>
            <h2>Pacote Ouro Branco</h2>
          </div>
          <div class="comentarios">
            <i class="fa-solid fa-comments"></i>
          </div>
        </div>
        <div id="itens">
          <p>Peso: 1000g</p>
          <p>Quantidade: 2087</p>
          <p>Preço: R$ 50,99</p>
        </div>
        <div class="botoes">
          <i class="fa-solid fa-square-xmark"></i>
          <i class="fa-solid fa-square-pen"></i>
        </div>
        <p class="descricao">Bombom com cobertura de chocolate branco e recheio cremoso à base de chocolate e castanha de caju. Textura crocante na casca e cremosa no interior.</p>
      </div>
    </div> -->
  </div>

  <footer>
    <div id="footer">

      <div class="contato">
        <h2>Informações de Contato</h2>
        <p><strong>Candy Stock:</strong></p>
        <p>Endereço: Av. alguma, 90, Caçapava - SP, 12084-090</p>
        <p>Telefone: (12) 9953-1976</p>
        <p>E-mail: candystock@gmail.com</p>

      </div>

      <div class="equipe">
        <h2>Equipe Desenvolvedora</h2>
        <ul>
          <p>Ana Lívia dos Santos Lopes</p>
          <p>Gabriel Reis de Brito</p>
          <p>Isadora Gomes da Silva</p>
          <p>Lucas Randal Abreu Balderrama</p>
          <p>Guilherme Ricardo de Paiva</p>
          <p>Flávia Glenda Guimarães Carvalho</p>
        </ul>
      </div>

    </div>
  </footer>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $('.button').click(function () {
      Swal.fire({
        title: 'Adicione um novo produto',
        confirmButtonText: 'Salvar produto',
        width: '950px',
        html: `<div class='swal2-input-container'>
          <div class= "pt1">

        <label for="swal-input1">Nome do produto</label>
        <input type='text' id='input-produto-nome' class='swal2-input' placeholder='Bala FINI Beijos'>
        <div id="div-pq">
          <div>
            <label for="swal-input1">Preço</label>
            <input type='text' id='input-produto-preco' class="pq" placeholder='100,00'>
          </div>
          <div id="qnt">
            <label for="swal-input1" >Estoque inicial</label>
            <input type='number' id='input-produto-estoque' class='swal2-input' placeholder='200' value='0'>
          </div>
        </div>

        <label for="swal-input1">Imagem do produto</label>
        <input type='file' id='input-produto-imagem' class='swal2-input' placeholder='Bala FINI Beijos'>
      </div>

      <div class="pt2">
        <div id="desc">
          <label for="swal-input1">Descrição</label>
          <input type='text' id='input-produto-descricao' class='swal2-input' placeholder='Descreva detalhes sobre o produto'>
        </div>

        <!-- <label for="swal-input1">Status</label>
        <select name="" id="input-produto-status">
          <option value="ativo">Ativo</option>
          <option value="inativo">Inativo</option>
        </select> -->
      </div>
    </div>`,
        confirmButtonColor: '#864B93',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
        preConfirm: async (name) => {
          const nome = document.getElementById('input-produto-nome').value;
          const preco = document.getElementById('input-produto-preco').value;
          const estoque = document.getElementById('input-produto-estoque').value;
          const descricao = document.getElementById('input-produto-descricao').value;
          // const status = document.getElementById('input-produto-status').value;
          const imagemInput = document.getElementById('input-produto-imagem');
          const imagem = imagemInput.files[0];

          if (!nome || !preco || !estoque || !descricao || !imagem) {
            Swal.showValidationMessage('Preencha todos os campos.');
            return false;
          }

          const formData = new FormData();
          formData.append('nome', nome);
          formData.append('preco', preco);
          formData.append('estoque', estoque);
          formData.append('descricao', descricao);
          formData.append('imagem', imagem);
          // formData.append('status', status);

          const response = await (await fetch("./adicionar_produto.php", {
            method: "POST",
            body: formData
          })).json();

          if (response.status === "success") {
            // Adicionar produto à lista sem reload
            adicionarProdutoNaTela(response.data);
            
            // Atualizar estatísticas
            atualizarEstatisticas();
            
            Swal.fire({
              icon: "success",
              title: "Sucesso ao adicionar produto",
              text: response.message
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Erro ao adicionar produto",
              text: response.message
            });
          }

          return true;          
        },
      })
    });


    function formatarDataHora(data) {
      const dia = String(data.getDate()).padStart(2, '0');
      const mes = String(data.getMonth() + 1).padStart(2, '0'); // Os meses são baseados em zero
      const ano = data.getFullYear();
      const horas = String(data.getHours()).padStart(2, '0');
      const minutos = String(data.getMinutes()).padStart(2, '0');
      const segundos = String(data.getSeconds()).padStart(2, '0');

      return `${dia}/${mes}/${ano} ${horas}:${minutos}:${segundos}`;
    }

    $('.comentarios').click(async function () {
      const id = this.getAttribute("id-produto");
      const comentarios = await (await fetch("./get_comentarios.php?id=" + id)).json();
      const htmlComentarios = [];
      for (const comentario of comentarios.data) {
        const data = new Date(comentario.data_hora);
        htmlComentarios.push(`
      <div>
        <div class="cima-comt">
          <h5>${comentario.usuario.nome}</h5>
          <p>${formatarDataHora(data)}</p>
        </div>
        <p>${comentario.conteudo}</p>
      </div>`);
      }
      Swal.fire({
        title: 'Comentários',
        confirmButtonText: 'Comentar',
        width: '950px',
        html: `<div class='swal2-comt-container'>
      ${htmlComentarios.join("<br>")}
      <div>
          <input type='text' id='swal-input1' class='swal2-input-3' placeholder='Adicione um comentário'>
      </div>
    </div>`,
        confirmButtonColor: '#864B93',
        showCancelButton: false,
        preConfirm: async () => {
          const conteudo = document.getElementById('swal-input1').value;
          if (!conteudo) {
            Swal.showValidationMessage('O comentário não pode ser vazio.');
            return false;
          }

          try {
            const formData = new FormData();
            formData.append('id_produto', id);
            formData.append('conteudo', conteudo);

            const response = await fetch("./adicionar_comentario.php", {
              method: "POST",
              body: formData
            });

            const data = await response.json();

            if (data.status === "success") {
              Swal.fire({
                icon: 'success',
                title: 'Sucesso ao adicionar comentário',
                text: data.message
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Erro ao adicionar comentário',
                text: data.message
              });
            }
          } catch (error) {
            console.log(error)
            Swal.fire({
              icon: 'error',
              title: 'Erro ao adicionar comentário',
              text: 'Ocorreu um erro local ao tentar adicionar o comentário.'
            });
          }

          return true;
        }
      })
    });


    $('.fa-square-xmark').click(function () {
      const id = this.getAttribute("id-produto");
      Swal.fire({
        title: "Tem certeza que deseja excluir?",
        text: "essa ação não podera ser revertida",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch("./excluir_produto.php", {
              method: "POST",
              body: formData
            });

            const data = await response.json();

            if (data.status === "success") {
              Swal.fire({
                title: "Excluído!",
                text: data.message,
                icon: "success"
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                title: "Erro!",
                text: data.message,
                icon: "error"
              });
            }
          } catch (error) {
            Swal.fire({
              title: "Erro!",
              text: "Ocorreu um erro ao excluir o produto.",
              icon: "error"
            });
          }
        }
      });
    });



    $('.fa-square-pen').click(async function () {
      const id = this.getAttribute("id-produto");
      
      try {
        // Buscar dados do produto
        const produtoResponse = await fetch(`./buscar_produto.php?id=${id}`);
        const produtoData = await produtoResponse.json();
        
        if (produtoData.status !== "success") {
          Swal.fire({
            title: "Erro!",
            text: produtoData.message,
            icon: "error"
          });
          return;
        }
        
        const produto = produtoData.data;
        
        Swal.fire({
          title: 'Editar produto',
          confirmButtonText: 'Salvar produto',
          width: '780px',
          html: `<div class='swal2-input-container'>
            <div class= "pt1">

          <label for="edit-nome">Nome do produto</label>
          <input type='text' id='edit-nome' class='swal2-input' value='${produto.nome}'>
          <div id="div-pq">
            <div>
              <label for="edit-preco">Preço</label> <br>
              <input type='number' id='edit-preco' class="pq" value='${produto.preco}' step='0.01'>
            </div>

          </div>

          <label for="edit-imagem">Imagem do produto</label>
          <input type='file' id='edit-imagem' class='swal2-input' accept='image/*'>
          <small style='color: #666; font-size: 0.8em;'>Atual: ${produto.imagem.split('/').pop()}</small>
        </div>

        <div class="pt2">
          <div id="desc">
            <label for="edit-descricao">Descrição</label> <br>
            <input type='text' id='edit-descricao' class='swal2-input' value='${produto.descricao}'>
          </div>

          <label for="edit-ativo">Status</label>
          <select id="edit-ativo">
            <option value="true" ${produto.ativo ? 'selected' : ''}>Ativo</option>
            <option value="false" ${!produto.ativo ? 'selected' : ''}>Inativo</option>
          </select>
        </div>
      </div>`,
          confirmButtonColor: '#864B93',
          cancelButtonText: 'Cancelar',
          showCancelButton: true,
          preConfirm: async () => {
            const nome = document.getElementById('edit-nome').value;
            const preco = document.getElementById('edit-preco').value;
            const descricao = document.getElementById('edit-descricao').value;
            const ativo = document.getElementById('edit-ativo').value;
            const imagem = document.getElementById('edit-imagem').files[0];

            try {
              const formData = new FormData();
              formData.append('id', id);
              
              if (nome.trim()) formData.append('nome', nome.trim());
              if (preco) formData.append('preco', preco);
              if (descricao.trim()) formData.append('descricao', descricao.trim());
              formData.append('ativo', ativo);
              if (imagem) formData.append('imagem', imagem);

              const response = await fetch("./editar_produto.php", {
                method: "POST",
                body: formData
              });

              const data = await response.json();

              if (data.status === "success") {
                Swal.fire({
                  title: "Editado!",
                  text: data.message,
                  icon: "success"
                }).then(() => {
                  location.reload();
                });
              } else {
                Swal.fire({
                  title: "Erro!",
                  text: data.message,
                  icon: "error"
                });
              }
            } catch (error) {
              Swal.fire({
                title: "Erro!",
                text: "Ocorreu um erro ao editar o produto.",
                icon: "error"
              });
            }

            return false;
          }
        });
      } catch (error) {
        Swal.fire({
          title: "Erro!",
          text: "Erro ao carregar dados do produto.",
          icon: "error"
        });
      }
    });



     $('.fa-square-plus').click(function () {
      const id = this.getAttribute("id-produto");
      Swal.fire({
        title: 'Adicionar quantidade de produto',
        confirmButtonText: 'Adicionar ao estoque',
        width: '650px',
        html: `<div class='swal2-input-container'>

       <div id="quantidade">
            <label for="add-quantidade" >Quantidade</label> <br>
            <input type='number' id='add-quantidade' class='swal2-input' placeholder='200' value='1' min='1'>
          </div>
    </div>`,
        confirmButtonColor: '#864B93',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
        preConfirm: async () => {
          const quantidade = document.getElementById('add-quantidade').value;
          
          if (!quantidade || quantidade <= 0) {
            Swal.showValidationMessage('A quantidade deve ser maior que zero.');
            return false;
          }

          try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('quantidade', quantidade);

            const response = await fetch("./adicionar_estoque.php", {
              method: "POST",
              body: formData
            });

            const data = await response.json();

            if (data.status === "success") {
              // Atualizar quantidade na tela
              const produtoElement = $(`.fa-square-plus[id-produto="${id}"]`).closest('.caixa-produto');
              const quantidadeAtual = parseInt(produtoElement.find('#itens p:first').text().replace('Quantidade: ', ''));
              const novaQuantidade = quantidadeAtual + parseInt(quantidade);
              produtoElement.find('#itens p:first').text(`Quantidade: ${novaQuantidade}`);
              
              // Atualizar estatísticas
              atualizarEstatisticas();
              
              Swal.fire({
                title: "Adicionado!",
                text: data.message,
                icon: "success"
              });
            } else {
              Swal.fire({
                title: "Erro!",
                text: data.message,
                icon: "error"
              });
            }
          } catch (error) {
            console.log(error);
            Swal.fire({
              title: "Erro!",
              text: "Ocorreu um erro ao adicionar ao estoque.",
              icon: "error"
            });
          }

          return false;
        }
      });
    });



         $('.fa-square-minus').click(function () {
      const id = this.getAttribute("id-produto");
      Swal.fire({
        title: 'Remover quantidade de produto',
        confirmButtonText: 'Remover do estoque',
        width: '650px',
        html: `<div class='swal2-input-container'>

       <div id="quantidade">
            <label for="rem-quantidade" >Quantidade</label> <br>
            <input type='number' id='rem-quantidade' class='swal2-input' placeholder='200' value='1' min='1'>
          </div>
    </div>`,
        confirmButtonColor: '#864B93',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
        preConfirm: async () => {
          const quantidade = document.getElementById('rem-quantidade').value;
          
          if (!quantidade || quantidade <= 0) {
            Swal.showValidationMessage('A quantidade deve ser maior que zero.');
            return false;
          }

          try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('quantidade', quantidade);

            const response = await fetch("./remover_estoque.php", {
              method: "POST",
              body: formData
            });

            const data = await response.json();

            if (data.status === "success") {
              // Atualizar quantidade na tela
              const produtoElement = $(`.fa-square-minus[id-produto="${id}"]`).closest('.caixa-produto');
              const quantidadeAtual = parseInt(produtoElement.find('#itens p:first').text().replace('Quantidade: ', ''));
              const novaQuantidade = quantidadeAtual - parseInt(quantidade);
              produtoElement.find('#itens p:first').text(`Quantidade: ${novaQuantidade}`);
              
              // Atualizar estatísticas
              atualizarEstatisticas();
              
              Swal.fire({
                title: "Removido!",
                text: data.message,
                icon: "success"
              });
            } else {
              Swal.fire({
                title: "Erro!",
                text: data.message,
                icon: "error"
              });
            }
          } catch (error) {
            Swal.fire({
              title: "Erro!",
              text: "Ocorreu um erro ao remover do estoque.",
              icon: "error"
            });
          }

          return false;
        }
      });
    });

    // Função para adicionar produto na tela dinamicamente
    function adicionarProdutoNaTela(produto) {
      const produtoHtml = `
        <div class="caixa-produto">
          <div id="caixa-img">
            <img src="${produto.imagem}" alt="${produto.nome}">
          </div>
          <div class="info">
            <div class="comment">
              <div>
                <h2>${produto.nome}</h2>
              </div>
              <div class="comentarios" id-produto="${produto.id}">
                <i class="fa-solid fa-comments"></i>
              </div>
            </div>
            <div id="itens">
              <p>Quantidade: ${produto.estoque}</p>
              <p>Preço: R$ ${produto.preco}</p>
            </div>
            <div class="botoes">
              <i class="fa-solid fa-square-xmark" id-produto="${produto.id}"></i>
              <i class="fa-solid fa-square-pen" id-produto="${produto.id}"></i>
              <i class="fa-solid fa-square-plus" id-produto="${produto.id}"></i>
              <i class="fa-solid fa-square-minus" id-produto="${produto.id}"></i>
            </div>
            <p class="descricao">${produto.descricao}</p>
          </div>
        </div>
      `;
      
      // Inserir após o botão de adicionar produto
      $('.button').after(produtoHtml);
      
      // Reativar eventos para os novos botões
      ativarEventosBotoes();
    }

    // Função para atualizar as estatísticas
    async function atualizarEstatisticas() {
      try {
        const response = await fetch("./get_estatisticas.php");
        const data = await response.json();
        
        if (data.status === "success") {
          const stats = data.data;
          
          // Atualizar contadores na tela
          $('.fa-box-archive').parent().html(`<i class="fa-solid iconresumo fa-box-archive"></i> ${stats.total} Total`);
          $('.fa-circle-check').parent().html(`<i class="fa-solid iconresumo fa-circle-check"></i> ${stats.sem_falta} Sem falta`);
          $('.fa-circle-xmark').parent().html(`<i class="fa-solid iconresumo fa-circle-xmark"></i> ${stats.em_falta} Em falta`);
        }
      } catch (error) {
        console.error('Erro ao atualizar estatísticas:', error);
      }
    }

    // Função para reativar eventos nos botões dos novos produtos
    function ativarEventosBotoes() {
      // Remover eventos duplicados e reativar
      $('.fa-square-xmark, .fa-square-pen, .fa-square-plus, .fa-square-minus, .comentarios').off();
      
      // Reativar todos os eventos
      ativarEventosComentarios();
      ativarEventosExcluir();
      ativarEventosEditar();
      ativarEventosAdicionarEstoque();
      ativarEventosRemoverEstoque();
    }

    // Separar eventos em funções para reuso
    function ativarEventosComentarios() {
      $('.comentarios').off('click').on('click', async function () {
        const id = this.getAttribute("id-produto");
        const comentarios = await (await fetch("./get_comentarios.php?id=" + id)).json();
        const htmlComentarios = [];
        for (const comentario of comentarios.data) {
          const data = new Date(comentario.data_hora);
          htmlComentarios.push(`
        <div>
          <div class="cima-comt">
            <h5>${comentario.usuario.nome}</h5>
            <p>${formatarDataHora(data)}</p>
          </div>
          <p>${comentario.conteudo}</p>
        </div>
        `);
        }

        Swal.fire({
          title: 'Comentários',
          confirmButtonText: 'Comentar',
          width: '650px',
          html: `<div class='swal2-input-container'>
        <div class="comentarios-list">
          ${htmlComentarios.length > 0 ? htmlComentarios.join('') : '<p>Nenhum comentário ainda.</p>'}
        </div>
        <div id="novo-comentario">
            <input type='text' id='swal-input1' class='swal2-input-3' placeholder='Adicione um comentário'>
        </div>
      </div>`,
          confirmButtonColor: '#864B93',
          showCancelButton: false,
          preConfirm: async () => {
            const conteudo = document.getElementById('swal-input1').value;
            if (!conteudo) {
              Swal.showValidationMessage('O comentário não pode ser vazio.');
              return false;
            }

            try {
              const formData = new FormData();
              formData.append('id_produto', id);
              formData.append('conteudo', conteudo);

              const response = await fetch("./adicionar_comentario.php", {
                method: "POST",
                body: formData
              });

              const data = await response.json();

              if (data.status === "success") {
                Swal.fire({
                  icon: 'success',
                  title: 'Sucesso ao adicionar comentário',
                  text: data.message
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Erro ao adicionar comentário',
                  text: data.message
                });
              }
            } catch (error) {
              console.log(error)
              Swal.fire({
                icon: 'error',
                title: 'Erro ao adicionar comentário',
                text: 'Ocorreu um erro local ao tentar adicionar o comentário.'
              });
            }

            return true;
          }
        });
      });
    }

    function ativarEventosExcluir() {
      $('.fa-square-xmark').off('click').on('click', function () {
        const id = this.getAttribute("id-produto");
        const produtoElement = $(this).closest('.caixa-produto');
        
        Swal.fire({
          title: "Tem certeza que deseja excluir?",
          text: "essa ação não podera ser revertida",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Sim, excluir",
          cancelButtonText: "Cancelar"
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const formData = new FormData();
              formData.append('id', id);

              const response = await fetch("./excluir_produto.php", {
                method: "POST",
                body: formData
              });

              const data = await response.json();

              if (data.status === "success") {
                // Remover produto da tela
                produtoElement.remove();
                
                // Atualizar estatísticas
                atualizarEstatisticas();
                
                Swal.fire({
                  title: "Excluído!",
                  text: data.message,
                  icon: "success"
                });
              } else {
                Swal.fire({
                  title: "Erro!",
                  text: data.message,
                  icon: "error"
                });
              }
            } catch (error) {
              Swal.fire({
                title: "Erro!",
                text: "Ocorreu um erro ao excluir o produto.",
                icon: "error"
              });
            }
          }
        });
      });
    }

    function ativarEventosEditar() {
      // Código do evento de editar já existente...
    }

    function ativarEventosAdicionarEstoque() {
      // Código do evento de adicionar estoque já existente...
    }

    function ativarEventosRemoverEstoque() {
      // Código do evento de remover estoque já existente...
    }


  </script>

</body>

</html>