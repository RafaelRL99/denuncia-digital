// Endpoint do backend PHP. O caminho funciona quando o projeto está no htdocs do XAMPP.
const API_URL = '../backend/cadastro.php';

let graficoTipos = null;
let graficoPlataformas = null;

// Envia a denúncia anônima para o backend usando Fetch API.
async function enviarDenuncia(event) {
  event.preventDefault();

  const formulario = event.target;
  const mensagem = document.querySelector('#mensagemFormulario');
  const dados = new FormData(formulario);
  dados.append('acao', 'cadastrar');

  mensagem.className = 'form-message';
  mensagem.textContent = 'Enviando denúncia...';

  try {
    const resposta = await fetch(API_URL, {
      method: 'POST',
      body: dados
    });

    const resultado = await resposta.json();

    if (!resposta.ok || !resultado.sucesso) {
      throw new Error(resultado.mensagem || 'Não foi possível cadastrar a denúncia.');
    }

    mensagem.className = 'form-message success';
    mensagem.textContent = resultado.mensagem;
    formulario.reset();
    await carregarDashboard();
  } catch (erro) {
    mensagem.className = 'form-message error';
    mensagem.textContent = erro.message;
  }
}

// Busca os totais no MySQL por meio do PHP e atualiza os gráficos.
async function carregarDashboard() {
  const mensagemDashboard = document.querySelector('#mensagemDashboard');
  mensagemDashboard.textContent = '';

  try {
    const resposta = await fetch(`${API_URL}?acao=estatisticas`);
    const dados = await resposta.json();

    if (!resposta.ok || !dados.sucesso) {
      throw new Error(dados.mensagem || 'Não foi possível carregar o dashboard.');
    }

    document.querySelector('#totalDenuncias').textContent = dados.total;
    renderizarGraficos(dados.por_tipo, dados.por_plataforma);
  } catch (erro) {
    mensagemDashboard.textContent = 'Não foi possível carregar os dados do banco.';
  }
}

// Cria ou atualiza os gráficos do Chart.js.
function renderizarGraficos(porTipo, porPlataforma) {
  const cores = ['#0f766e', '#b45309', '#2563eb', '#be123c', '#4d7c0f', '#6d28d9'];

  const labelsTipo = porTipo.map(item => item.rotulo);
  const valoresTipo = porTipo.map(item => item.total);
  const labelsPlataforma = porPlataforma.map(item => item.rotulo);
  const valoresPlataforma = porPlataforma.map(item => item.total);

  if (graficoTipos) {
    graficoTipos.destroy();
  }

  if (graficoPlataformas) {
    graficoPlataformas.destroy();
  }

  graficoTipos = new Chart(document.querySelector('#graficoTipos'), {
    type: 'bar',
    data: {
      labels: labelsTipo,
      datasets: [{
        label: 'Denúncias',
        data: valoresTipo,
        backgroundColor: cores
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 }
        }
      }
    }
  });

  graficoPlataformas = new Chart(document.querySelector('#graficoPlataformas'), {
    type: 'doughnut',
    data: {
      labels: labelsPlataforma,
      datasets: [{
        label: 'Denúncias',
        data: valoresPlataforma,
        backgroundColor: cores
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
}

// Inicialização da página depois que todo o HTML foi carregado.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('#formDenuncia').addEventListener('submit', enviarDenuncia);
  carregarDashboard();
});
