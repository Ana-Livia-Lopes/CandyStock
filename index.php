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
        <li><a href="#">Produtos</a></li>
        <li><a href="#">Relatórios</a></li>
        <li><a href="#">Perfil</a></li>
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
          <i class="fa-solid fa-square-xmark"></i>
          <i class="fa-solid fa-square-pen"></i>
          <i class="fa-solid fa-square-plus"></i>
          <i class="fa-solid fa-square-minus"></i>
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
        <p class="descricao">Bala sabor framboesa, de consistência firme e coloração rosada. Possui aroma característico
          e sabor artificial de fruta. Embalada individualmente.</p>
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
        <p class="descricao">Bombom com cobertura de chocolate branco e recheio cremoso à base de chocolate e castanha
          de caju. Textura crocante na casca e cremosa no interior.</p>
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

          Swal.fire({
            icon: response.status,
            title: (response.status === "success" ? "Sucesso" : "Erro") + " ao adicionar produto",
            text: response.message
          });

          return true;          
        },
      })
    });




    $('.comentarios').click(function () {
      Swal.fire({
        title: 'Comentários',
        confirmButtonText: 'Comentar',
        width: '950px',
        html: `<div class='swal2-comt-container'>
      <div>
        <div class="cima-comt">
          <h5>evelynmontes18</h5>
          <p>2h</p>
        </div>
        <p>Esse bombom é o melhor, compensa muito comprar o pacote</p>
      </div>
      <hr>
      <div>
        <div class="cima-comt">
          <h5>rogeriofranca</h5>
          <p>1d</p>
        </div>
        <p>Comprei 4 pacotes desse pro aniversário da minha filha.</p>
      </div>
      <hr>
      <div>
        <div class="cima-comt">
          <h5>julianasouza01</h5>
          <p>2d</p>
        </div>
        <p>Melhor preço que tem é nessa loja</p>
      </div>
      <div>
          <input type='text' id='swal-input1' class='swal2-input-3' placeholder='Adicione um comentário'>
      </div>
    </div>`,
        confirmButtonColor: '#864B93',
        showCancelButton: false,
      })
    });


    $('.fa-square-xmark').click(function () {
      Swal.fire({
        title: "Tem certeza que deseja excluir?",
        text: "essa ação não podera ser revertida",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Excluido!",
            text: "o produto foi excluido de estoque.",
            icon: "success"
          });
        }
      });
    });



    $('.fa-square-pen').click(function () {
      Swal.fire({
        title: 'Editar produto',
        confirmButtonText: 'Salvar produto',
        width: '780px',
        html: `<div class='swal2-input-container'>
          <div class= "pt1">

        <label for="swal-input1">Nome do produto</label>
        <input type='text' id='swal-input1' class='swal2-input' placeholder='Bala FINI Beijos'>
        <div id="div-pq">
          <div>
            <label for="swal-input1">Preço</label> <br>
            <input type='text' id='swal-input1' class="pq" placeholder='100,00'>
          </div>

        </div>

        <label for="swal-input1">Imagem do produto</label>
        <input type='file' id='swal-input1' class='swal2-input' placeholder='Bala FINI Beijos'>
      </div>

      <div class="pt2">
        <div id="desc">
          <label for="swal-input1">Descrição</label> <br>
          <input type='text' id='swal-input1' class='swal2-input' placeholder='Descreva detalhes sobre o produto'>
        </div>

        <label for="swal-input1">Status</label>
        <select name="" id="select">
          <option value="">Ativo</option>
          <option value="">Inativo</option>
        </select>
      </div>
    </div>`,
        confirmButtonColor: '#864B93',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Editado!",
            text: "o produto foi editado no estoque.",
            icon: "success"
          });
        }
      });
    });



     $('.fa-square-plus').click(function () {
      Swal.fire({
        title: 'Adicionar quantidade de produto',
        confirmButtonText: 'Salvar produto',
        width: '650px',
        html: `<div class='swal2-input-container'>

       <div id="quantidade">
            <label for="swal-input1" >Quantidade</label> <br>
            <input type='number' id='input-produto-estoque' class='swal2-input' placeholder='200' value='0'>
          </div>
    </div>`,
        confirmButtonColor: '#864B93',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Adicionado!",
            text: "o produto foi adicionado no estoque.",
            icon: "success"
          });
        }
      });
    });



         $('.fa-square-minus').click(function () {
      Swal.fire({
        title: 'Remover quantidade de produto',
        confirmButtonText: 'Salvar produto',
        width: '650px',
        html: `<div class='swal2-input-container'>

       <div id="quantidade">
            <label for="swal-input1" >Quantidade</label> <br>
            <input type='number' id='input-produto-estoque' class='swal2-input' placeholder='200' value='0'>
          </div>
    </div>`,
        confirmButtonColor: '#864B93',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Removido!",
            text: "o produto foi removido no estoque.",
            icon: "success"
          });
        }
      });
    });


  </script>

</body>

</html>