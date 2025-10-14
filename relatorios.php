<?php
include 'php/features.php';

// Verificar se o usuário está logado
if (!Usuario::hasSessao()) {
    header('Location: login.php');
    exit;
}

// Buscar dados para relatórios
$produtos = Produto::pesquisar(ativo: true);

// Calcular estatísticas
$totalProdutos = count($produtos);
$produtosComEstoqueBaixo = 0;
$produtosRanking = [];

foreach ($produtos as $produto) {
    if ($produto->hasEstoqueBaixo()) {
        $produtosComEstoqueBaixo++;
    }

    $produtosRanking[] = [
        'nome' => $produto->getNome(),
        'estoque' => $produto->getEstoque(),
        'preco' => $produto->getPreco()
    ];
}

// Ordenar por estoque (maior para menor)
usort($produtosRanking, function ($a, $b) {
    return $b['estoque'] - $a['estoque'];
});

// Buscar movimentações dos últimos 30 dias
global $conn;
$sql = "SELECT m.*, p.nome as produto_nome, m.direcao, m.quantidade, m.data_hora 
        FROM movimentacoes m 
        JOIN produtos p ON m.id_produto = p.id 
        WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY m.data_hora DESC";
$stmt = $conn->prepare($sql);
$movimentacoes = [];

if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $movimentacoes[] = $row;
    }
}

// Calcular totais de entrada e saída
$totalEntradas = 0;
$totalSaidas = 0;
$movimentacoesPorProduto = [];

foreach ($movimentacoes as $mov) {
    if ($mov['direcao'] === 'entrada') {
        $totalEntradas += $mov['quantidade'];
        $movimentacoesPorProduto[$mov['produto_nome']]['entradas'] =
            ($movimentacoesPorProduto[$mov['produto_nome']]['entradas'] ?? 0) + $mov['quantidade'];
    } else {
        $totalSaidas += $mov['quantidade'];
        $movimentacoesPorProduto[$mov['produto_nome']]['saidas'] =
            ($movimentacoesPorProduto[$mov['produto_nome']]['saidas'] ?? 0) + $mov['quantidade'];
    }
}

// Encontrar produto mais e menos movimentado
$produtoMaisVendido = '';
$quantidadeMaisVendida = 0;
$produtoMenosVendido = '';
$quantidadeMenosVendida = PHP_INT_MAX;

foreach ($movimentacoesPorProduto as $nome => $dados) {
    $saidas = $dados['saidas'] ?? 0;
    if ($saidas > $quantidadeMaisVendida) {
        $quantidadeMaisVendida = $saidas;
        $produtoMaisVendido = $nome;
    }
    if ($saidas < $quantidadeMenosVendida && $saidas > 0) {
        $quantidadeMenosVendida = $saidas;
        $produtoMenosVendido = $nome;
    }
}

// Preparar dados para o gráfico de linha (últimos 8 períodos de 3 dias cada)
$dadosGrafico = [];
$labels = [];

for ($i = 7; $i >= 0; $i--) {
    $dataInicio = date('Y-m-d', strtotime("-" . (($i + 1) * 3) . " days"));
    $dataFim = date('Y-m-d', strtotime("-" . ($i * 3) . " days"));

    // Label para exibição
    $labels[] = date('d/m', strtotime($dataFim));

    // Buscar movimentações neste período
    $sql = "SELECT direcao, SUM(quantidade) as total
            FROM movimentacoes 
            WHERE DATE(data_hora) BETWEEN ? AND ?
            GROUP BY direcao";
    $stmt = $conn->prepare($sql);

    $entradas = 0;
    $saidas = 0;

    if ($stmt) {
        $stmt->bind_param("ss", $dataInicio, $dataFim);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if ($row['direcao'] === 'entrada') {
                    $entradas = (int) $row['total'];
                } else {
                    $saidas = (int) $row['total'];
                }
            }
        }
    }

    $dadosGrafico[] = [
        'entradas' => $entradas,
        'saidas' => $saidas
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - CandyStock</title>
    <link rel="stylesheet" href="./css/relatorios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rethink+Sans:ital,wght@0,400..800;1,400..800&family=Space+Grotesk:wght@300..700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="menu">
        <div>
            <img src="./img/logo-candy-senfundo.png" alt="logo" class="logo">
            <p>Relatórios</p>
        </div>
        <i class="fa-solid fa-bars" onclick="toggleMenu()"></i>
        <ul>
            <li><a href="index.php">Produtos</a></li>
            <li><a href="relatorios.php">Relatórios</a></li>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="sair.php">Sair</a></li>
        </ul>
    </div>

    <main>
        <div class="container-cima">
            <div class="box-graficoArea">
                <h2>Histórico de entradas/saídas</h2>
                <canvas id="graficoArea"></canvas>
            </div>
            <div class="dados">
                <h2>Dados do período (últimos 30 dias)</h2>
                <div>
                    <p><span style="font-weight: bold;">Total de entradas: </span><?= $totalEntradas ?></p>
                    <p><span style="font-weight: bold;">Total de saídas: </span><?= $totalSaidas ?></p>
                    <p><span style="font-weight: bold;">Diferença: </span><?= $totalEntradas - $totalSaidas ?></p>
                </div>
                <hr>
                <div>
                    <p><span style="font-weight: bold;">Produto mais vendido: </span><?= $produtoMaisVendido ?: 'N/A' ?>
                    </p>
                    <p><span style="font-weight: bold;">Quantidade vendida: </span><?= $quantidadeMaisVendida ?: 0 ?>
                    </p>
                    <p><span style="font-weight: bold;">Produto menos vendido:
                        </span><?= $produtoMenosVendido ?: 'N/A' ?></p>
                    <p><span style="font-weight: bold;">Quantidade vendida:
                        </span><?= $quantidadeMenosVendida < PHP_INT_MAX ? $quantidadeMenosVendida : 0 ?></p>
                    <a href="#rank-entrada"><button>Ver todos</button></a>
                </div>
                <hr>
                <div>
                    <p>O histórico de entradas e saídas é atualizado em tempo real.</p>
                </div>
            </div>
        </div>
        <div class="container-baixo">
            <div class="box-graficoPizza">
                <h2 style="margin-bottom: 3px !important;">Produtos: Saídas</h2>
                <p style="margin-bottom: 20px; color: rgb(141, 141, 141);">Últimos 30 dias</p>
                <canvas id="graficoPizza"></canvas>
            </div>
            <div class="box-graficoColuna">
                <h2>Estoque Atual vs Crítico</h2>
                <canvas id="graficoColuna"></canvas>
            </div>
        </div>
        <div id="rank-entrada">
            <h2 style="margin-bottom: 3px !important;">Ranking de produtos por estoque</h2>
            <p style="margin-bottom: 20px; color: rgb(141, 141, 141);">Ordenado por quantidade em estoque</p>
            <div class="label-ranking">
                <span>Posição</span>
                <span>Produto</span>
                <span>Estoque</span>
            </div>
            <ul id="rankingEstoque">
                <?php foreach ($produtosRanking as $index => $produto): ?>
                    <li>
                        <span><?= $index + 1 ?>º</span>
                        <span><?= htmlspecialchars($produto['nome']) ?></span>
                        <span><?= $produto['estoque'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>

    <footer>
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
                <a href="https://linktr.ee/analivialopess" target="_blank" class="conteudo-site">
                    <li>Ana Lívia dos Santos Lopes</li>
                </a>
                <a href="https://linktr.ee/flaviaglenda" target="_blank" class="conteudo-site">
                    <li>Flávia Glenda Guimarães Carvalho</li>
                </a>
                <a href="https://linktr.ee/gabrielreiss" target="_blank" class="conteudo-site">
                    <li>Gabriel Reis de Brito</li>
                </a>
                <a href="https://linktr.ee/guilhermedpaiva" target="_blank" class="conteudo-site">
                    <li>Guilherme Ricardo de Paiva</li>
                </a>
                <a href="https://linktr.ee/isadoragomess" target="_blank" class="conteudo-site">
                    <li>Isadora Gomes da Silva</li>
                </a>
                <a href="https://linktr.ee/lucasbalderrama" target="_blank" class="conteudo-site">
                    <li>Lucas Randal Abreu Balderrama</li>
                </a>
            </ul>
        </div>
    </footer>

    <script>
        function toggleMenu() {
            const menu = document.querySelector('.menu ul');
            menu.classList.toggle('active');
        }

        // Dados PHP para JavaScript
        const movimentacoesPorProduto = <?= json_encode($movimentacoesPorProduto) ?>;
        const produtosRanking = <?= json_encode($produtosRanking) ?>;
        const dadosGrafico = <?= json_encode($dadosGrafico) ?>;
        const labelsGrafico = <?= json_encode($labels) ?>;

        // Gráfico de linha - Histórico de entradas/saídas (DADOS REAIS)
        const ctx = document.getElementById('graficoArea');

        // Extrair dados reais do PHP
        const entradasData = dadosGrafico.map(periodo => periodo.entradas);
        const saidasData = dadosGrafico.map(periodo => periodo.saidas);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelsGrafico,
                datasets: [
                    {
                        label: 'Entradas',
                        data: entradasData,
                        fill: true,
                        backgroundColor: 'rgba(81, 100, 224, 0.4)',
                        borderColor: 'rgba(81, 100, 224, 1)',
                        tension: 0.4
                    },
                    {
                        label: 'Saídas',
                        data: saidasData,
                        fill: true,
                        backgroundColor: 'rgba(255, 25, 25, 0.4)',
                        borderColor: 'rgba(255, 25, 25, 1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            stepSize: 10
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de pizza - Saídas por produto
        const ctxPizza = document.getElementById('graficoPizza');
        const produtosNomes = [];
        const saidasPorProduto = [];
        const cores = [
            'rgba(81, 100, 224, 0.7)',
            'rgba(255, 25, 25, 0.7)',
            'rgba(48, 207, 77, 0.7)',
            'rgba(147, 81, 224, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(255, 99, 132, 0.7)'
        ];

        let colorIndex = 0;
        for (const [produto, dados] of Object.entries(movimentacoesPorProduto)) {
            if (dados.saidas > 0) {
                produtosNomes.push(produto);
                saidasPorProduto.push(dados.saidas);
                colorIndex++;
            }
        }

        new Chart(ctxPizza, {
            type: 'doughnut',
            data: {
                labels: produtosNomes,
                datasets: [{
                    label: 'Saídas',
                    data: saidasPorProduto,
                    backgroundColor: cores.slice(0, produtosNomes.length),
                    borderColor: cores.slice(0, produtosNomes.length).map(color => color.replace('0.7', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            padding: 15
                        }
                    }
                }
            }
        });

        // Gráfico de colunas - Estoque atual
        const ctxColuna = document.getElementById('graficoColuna');
        const nomesProdutos = produtosRanking.slice(0, 6).map(p => p.nome);
        const estoqueAtual = produtosRanking.slice(0, 6).map(p => p.estoque);

        new Chart(ctxColuna, {
            type: 'bar',
            data: {
                labels: nomesProdutos,
                datasets: [{
                    label: 'Estoque atual',
                    data: estoqueAtual,
                    backgroundColor: estoqueAtual.map(estoque =>
                        estoque < <?= Produto::ESTOQUE_BAIXO_LIMITE ?> ? 'rgba(255, 25, 25, 0.6)' : 'rgba(48, 207, 77, 0.6)'
                    ),
                    borderColor: estoqueAtual.map(estoque =>
                        estoque < <?= Produto::ESTOQUE_BAIXO_LIMITE ?> ? 'rgba(255, 25, 25, 1)' : 'rgba(48, 207, 77, 1)'
                    ),
                    borderWidth: 1,
                    borderRadius: 1
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 20 }
                    }
                }
            },
            plugins: [{
                id: 'linhaCritica',
                afterDraw: (chart) => {
                    const { ctx, chartArea: { left, right }, scales: { y } } = chart;
                    const yPos = y.getPixelForValue(<?= Produto::ESTOQUE_BAIXO_LIMITE ?>);

                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(left, yPos);
                    ctx.lineTo(right, yPos);
                    ctx.lineWidth = 2;
                    ctx.strokeStyle = 'rgba(255, 54, 54, 0.8)';
                    ctx.setLineDash([6, 6]);
                    ctx.stroke();
                    ctx.restore();
                }
            }]
        });
    </script>
</body>

</html>