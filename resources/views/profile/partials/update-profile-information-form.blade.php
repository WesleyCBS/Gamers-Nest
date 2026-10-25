<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Informações do Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ strtolower($user->role) === 'admin' ? 'Atualize o nome, e-mail e foto da sua conta.' : 'Atualize as informações de perfil, foto e endereço da sua conta.' }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="foto" :value="__('Foto de Perfil')" />
            
            <div class="flex items-center gap-4 mt-2">
                <div class="shrink-0">
                    <img id="preview-foto" 
                         src="{{ isset($user) && $user->foto ? Storage::url($user->foto) : 'https://via.placeholder.com/150' }}" 
                         alt="Foto de Perfil" 
                         class="w-16 h-16 rounded-full object-cover shadow-md border border-gray-200 dark:border-gray-700">
                </div>

                <div class="w-full">
                    <input id="foto" name="foto" type="file" accept="image/*" onchange="previewImage(event)" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
                    <x-input-error class="mt-2" :messages="$errors->get('foto')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        @if(strtolower($user->role) !== 'admin')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-input-label for="cep" :value="__('CEP')" />
                <x-text-input id="cep" name="cep" type="text" class="mt-1 block w-full" :value="old('cep', $user->cep)" maxlength="9" />
                <x-input-error class="mt-2" :messages="$errors->get('cep')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="logradouro" :value="__('Logradouro')" />
                <x-text-input id="logradouro" name="logradouro" type="text" class="mt-1 block w-full" :value="old('logradouro', $user->logradouro)" />
                <x-input-error class="mt-2" :messages="$errors->get('logradouro')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="numero" :value="__('Número')" />
                <x-text-input id="numero" name="numero" type="text" class="mt-1 block w-full" :value="old('numero', $user->numero)" />
                <x-input-error class="mt-2" :messages="$errors->get('numero')" />
            </div>
            <div>
                <x-input-label for="bairro" :value="__('Bairro')" />
                <x-text-input id="bairro" name="bairro" type="text" class="mt-1 block w-full" :value="old('bairro', $user->bairro)" />
                <x-input-error class="mt-2" :messages="$errors->get('bairro')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <x-input-label for="cidade" :value="__('Cidade')" />
                <x-text-input id="cidade" name="cidade" type="text" class="mt-1 block w-full" :value="old('cidade', $user->cidade)" />
                <x-input-error class="mt-2" :messages="$errors->get('cidade')" />
            </div>
            <div>
                <x-input-label for="uf" :value="__('UF')" />
                <x-text-input id="uf" name="uf" type="text" class="mt-1 block w-full" :value="old('uf', $user->uf)" maxlength="2" />
                <x-input-error class="mt-2" :messages="$errors->get('uf')" />
            </div>
        </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Salvo.') }}</p>
            @endif
        </div>
    </form>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('preview-foto');
                output.src = reader.result;
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        document.getElementById('cep')?.addEventListener('blur', function() {
            let cep = this.value.replace(/\D/g, '');

            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro || '';
                            document.getElementById('bairro').value = data.bairro || '';
                            document.getElementById('cidade').value = data.localidade || '';
                            document.getElementById('uf').value = data.uf || '';
                            document.getElementById('numero').focus();
                        } else {
                            alert('CEP não encontrado.');
                        }
                    })
                    .catch(error => console.error('Erro ao buscar o CEP:', error));
            }
        });
    </script>
</section>