document.getElementById('formSubir').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    const inputFile = document.getElementById('inputImagen');
    const preview = document.getElementById('preview');

    fetch('subir_imagen_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            mostrarMensaje('Imagen subida correctamente.', 'success');

            // Limpiar formulario y vista previa
            form.reset();
            preview.style.display = 'none';

            // Añadir imagen a la galería dinámicamente
            const galeria = document.querySelector('.galeria');

            const divCont = document.createElement('div');
            divCont.classList.add('imagen-container');

            const enlace = document.createElement('a');
            enlace.href = data.original;
            enlace.setAttribute('href', '#modal-' + md5(data.nombre));
            enlace.innerHTML = `<img src="${data.thumb}" alt="Imagen">`;

            // Modal
            const modal = document.createElement('div');
            modal.classList.add('modal');
            modal.id = 'modal-' + md5(data.nombre);
            modal.innerHTML = `
                <a href="#" class="modal-close">&times;</a>
                <img src="${data.original}" alt="Imagen grande">
            `;

            // Form eliminar
            const formEliminar = document.createElement('form');
            formEliminar.method = 'POST';
            formEliminar.onsubmit = () => confirm('¿Eliminar esta imagen?');
            formEliminar.innerHTML = `<input type="hidden" name="eliminar" value="${data.nombre}">
                                     <button class="btn-eliminar" type="submit">✖</button>`;

            divCont.appendChild(enlace);
            divCont.appendChild(formEliminar);
            galeria.prepend(divCont);
            document.body.appendChild(modal);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(err => alert('Error al subir la imagen: ' + err));
});

// Función simple para md5 (solo para IDs del modal)
function md5(str) {
    // Esta función es una versión simple, para producción usar una librería.
    return str.split('').reduce((hash, c) => {
        return ((hash << 5) - hash) + c.charCodeAt(0);
    }, 0).toString(16);
}

function mostrarMensaje(texto, tipo = 'success', duracion = 3000) {
    const mensaje = document.getElementById('mensaje');
    mensaje.textContent = texto;
    mensaje.className = tipo === 'error' ? 'error' : '';
    mensaje.style.opacity = '1';
    mensaje.style.pointerEvents = 'auto';

    setTimeout(() => {
        mensaje.style.opacity = '0';
        mensaje.style.pointerEvents = 'none';
    }, duracion);
}


// Mostrar vista previa
document.getElementById('inputImagen').addEventListener('change', function (event) {
  const preview = document.getElementById('preview');
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    preview.style.display = 'none';
  }
});
