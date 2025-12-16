<?php
class ModeradorIA {
    private $api_key;
    private $provider;
    private $cache = [];
    private $stats = [
        'total' => 0,
        'aprobados' => 0,
        'rechazados' => 0,
        'por_ia' => 0,
        'por_patrones' => 0,
        'tiempo_total' => 0
    ];
    
    public function __construct($provider = 'openai', $api_key = null) {
    $this->provider = $provider;

    switch ($provider) {
        case 'openai':
            $this->api_key = $api_key ?? getenv('OPENAI_API_KEY');
            break;

        case 'huggingface':
            $this->api_key = $api_key ?? getenv('HF_TOKEN');
            break;

        case 'patrones':
            $this->api_key = null;
            break;
    }

    if ($this->api_key === null && $provider !== 'patrones') {
        throw new Exception("API key no definida para el proveedor: $provider");
    }

    error_log("========================================");
    error_log("MODERADOR IA INICIALIZADO");
    error_log("Proveedor: " . strtoupper($provider));
    error_log("Filtros activos: 4 capas de protección");
    error_log("========================================");
}

    
    public function analizarComentario($texto) {
        $inicio = microtime(true);
        $this->stats['total']++;
        
        error_log("========================================");
        error_log("=== NUEVO ANÁLISIS ===");
        error_log("TEXTO: '" . $texto . "'");
        error_log("========================================");
        
        // Validación básica
        $texto = trim($texto);
        if (strlen($texto) < 2) {
            return $this->crearResultado(false, 'Comentario demasiado corto', 1.0, 'Validación');
        }
        
        // Verificar cache
        $hash = md5($texto);
        if (isset($this->cache[$hash])) {
            error_log("⚡ CACHE HIT");
            return $this->cache[$hash];
        }
        
        // Insultos leves
        error_log(">>> Capa 1: Verificando insultos leves...");
        $resultado = $this->detectarInsultosLeves($texto);
        if ($resultado !== null) {
            return $this->finalizarAnalisis($resultado, $inicio, $hash);
        }
        error_log("✓ Capa 1: Limpio");
        
        // Amenazas y violencia
        error_log(">>> Capa 2: Verificando amenazas...");
        $resultado = $this->detectarAmenazasYViolencia($texto);
        if ($resultado !== null) {
            return $this->finalizarAnalisis($resultado, $inicio, $hash);
        }
        error_log("✓ Capa 2: Limpio");
        
        // Acoso y humillación
        error_log(">>> Capa 3: Verificando acoso...");
        $resultado = $this->detectarAcosoYHumillacion($texto);
        if ($resultado !== null) {
            return $this->finalizarAnalisis($resultado, $inicio, $hash);
        }
        error_log("✓ Capa 3: Limpio");
        
        // Análisis con IA
        error_log(">>> Capa 4: Análisis con IA...");
        
        try {
            switch($this->provider) {
                case 'openai':
                    $resultado = $this->analizarConOpenAI($texto);
                    break;
                    
                case 'huggingface':
                    $resultado = $this->analizarConHuggingFace($texto);
                    break;
                    
                case 'patrones':
                default:
                    $resultado = $this->analizarConPatrones($texto);
                    break;
            }
            
            return $this->finalizarAnalisis($resultado, $inicio, $hash);
            
        } catch (Exception $e) {
            error_log("❌ ERROR EN IA: " . $e->getMessage());
            
            if ($this->provider !== 'patrones') {
                error_log(">>> Fallback a patrones");
                $resultado = $this->analizarConPatrones($texto);
                return $this->finalizarAnalisis($resultado, $inicio, $hash);
            }
            
            return $this->crearResultado(
                false,
                'Error en análisis - Requiere revisión manual',
                0.5,
                'Error'
            );
        }
    }
    
    // INSULTOS LEVES    
    private function detectarInsultosLeves($texto) {
        $textoNorm = $this->normalizarTexto($texto);
        
        $insultosComunes = [
            'idiota', 'estupido', 'estúpido', 'imbecil', 'imbécil',
            'tonto', 'tonta', 'bobo', 'boba', 'bruto', 'bruta',
            'estupida', 'estúpida', 'inutil', 'inútil', 'tarado', 'tarada',
            'retrasado', 'retrasada', 'mongoloide', 'subnormal',
            'pendejo', 'pendeja', 'puto', 'puta', 'cabron', 'cabrón',
            'cabrona', 'joder', 'mierda', 'cagada', 'chingada',
            'verga', 'huevon', 'huevón', 'wey', 'güey', 'menso', 'mensa',
            'basura', 'escoria', 'desgraciado', 'desgraciada',
            'miserable', 'rastrero', 'rastrera', 'lacra',
            'animal', 'bestia', 'payaso', 'ridiculo', 'ridículo',
            'ridicula', 'ridícula', 'patético', 'patetica', 'patética',
            'stupid', 'idiot', 'moron', 'dumb', 'loser',
            'asshole', 'bitch', 'bastard', 'fuck you'
        ];
        
        foreach ($insultosComunes as $insulto) {
            $patron = '/\b' . preg_quote($this->normalizarTexto($insulto), '/') . '\b/u';
            if (preg_match($patron, $textoNorm)) {
                error_log("❌ INSULTO detectado: '{$insulto}'");
                return $this->crearResultado(
                    false,
                    "Lenguaje irrespetuoso detectado - Por favor mantén un tono cordial",
                    0.88,
                    'Filtro_Insultos',
                    ['palabra_detectada' => $insulto]
                );
            }
        }
        
        // Patrones con caracteres especiales
        $patronesEvasion = [
            '/[i1!ı]\W*[d]\W*[i1!ı]\W*[o0]\W*[t]\W*[a4@]/iu',
            '/[e3€]\W*[s5$]\W*[t]\W*[u]\W*[p]\W*[i1!ı]\W*[d]\W*[o0]/iu',
        ];
        
        foreach ($patronesEvasion as $patron) {
            if (preg_match($patron, $textoNorm)) {
                error_log("❌ INSULTO con caracteres especiales detectado");
                return $this->crearResultado(
                    false,
                    "Lenguaje irrespetuoso detectado",
                    0.85,
                    'Filtro_Insultos',
                    ['palabra_detectada' => 'insulto con caracteres especiales']
                );
            }
        }
        
        return null;
    }
    
    // AMENAZAS Y VIOLENCIA    
    private function detectarAmenazasYViolencia($texto) {
        $textoNorm = $this->normalizarTexto($texto);
        
        $patronesAmenazas = [
            // Amenazas de muerte
            '/\b(te|voy a|vamos a|te voy a)\s*(matar|asesinar|eliminar|acabar contigo|liquidar)/iu',
            '/\b(muere|muérete|muerate|ojalá te mueras|espero que te mueras)/iu',
            '/\b(te mereces (la )?muerte|mereces morir)/iu',
            
            // Amenazas de violencia física
            '/\b(te voy a|voy a|vamos a)\s*(golpear|pegar|romper|partir|quebrar|chingar|madrea)/iu',
            '/\b(te voy a|voy a)\s*(buscar|encontrar|ir a tu casa)/iu',
            '/\b(cuidate|ten cuidado|te vas a arrepentir|ya veras|ya verás)/iu',
            
            // Amenazas sexuales
            '/\b(violar|violarte|te voy a violar)/iu',
            
            // Incitación al suicidio
            '/\b(suicidate|suicídate|matate|mátate|tirate|tírate|lanzate|lánzate|cortate las venas|córtate las venas)/iu',
            '/\b(debería(s)? suicidarte|mejor suicídate|mejor suicidate)/iu',
            
            // Deseos de muerte/daño
            '/\b(ojalá|espero que)\s*(te maten|te violen|te mueras|sufras|te pase algo)/iu',
            '/\b(que te maten|que te mueras|que sufras)/iu',
            
            // Amenazas grupales
            '/\b(a ti y a tu familia|tu familia va a|les voy a hacer)/iu',
            
            // Violencia extrema
            '/\b(torturar|secuestrar|explotar|bomba|ataque|disparar|balazo)/iu',
            
            // Inglés
            '/\b(i will kill you|i\'ll kill you|you should die|kill yourself|go die)/iu',
            '/\b(i will (beat|hurt|rape) you)/iu',
        ];
        
        foreach ($patronesAmenazas as $patron) {
            if (preg_match($patron, $textoNorm)) {
                error_log("❌❌❌ AMENAZA/VIOLENCIA detectada");
                return $this->crearResultado(
                    false,
                    "Amenaza o contenido violento detectado - Este tipo de contenido está estrictamente prohibido",
                    0.95,
                    'Filtro_Amenazas',
                    [
                        'palabra_detectada' => 'amenaza de violencia',
                        'categoria' => 'violence/threats'
                    ]
                );
            }
        }
        
        return null;
    }
    
    // ACOSO Y HUMILLACIÓN    
    private function detectarAcosoYHumillacion($texto) {
        $textoNorm = $this->normalizarTexto($texto);
        
        $patronesAcoso = [
            // Desvalorización personal
            '/\b(no vales|no sirves|no eres|no aportas)\s*(nada|para nada|un carajo|una mierda)/iu',
            '/\b(eres (un |una )?)(fracaso|fracasado|fracasada|perdedor|perdedora|loser)/iu',
            '/\b(no mereces|no te mereces)\s*(vivir|nada|estar aqui|estar aquí|respirar|existir)/iu',
            '/\b(eres (una |un )?)(escoria|desecho|desperdicio|cero a la izquierda)/iu',
            
            // Ataques a la inteligencia
            '/\b(no tienes|careces de)\s*(cerebro|inteligencia|neuronas|sentido comun|sentido común|razon|razón)/iu',
            '/\b(tu (mente|cerebro|cabeza))\s*(esta vacia|está vacía|no funciona|no sirve)/iu',
            '/\b(deberias|deberías|mejor)\s*(callarte|no hablar|cerrar la boca|quedarte callado)/iu',
            
            // Humillación directa
            '/\b(das (pena|lastima|lástima|asco|verguenza|vergüenza))/iu',
            '/\b(eres (una |un )?(verguenza|vergüenza|decepcion|decepción|patético|patetica|patética))/iu',
            '/\b(que (pena|lastima|lástima|asco|verguenza|vergüenza))\s*(de persona|contigo|das|me das)/iu',
            '/\b(me das|das)\s*(asco|nauseas|verguenza|vergüenza)/iu',
            
            // Comparaciones despectivas
            '/\b(pareces|eres como|actuas como|actúas como)\s*(un |una )?(animal|bestia|cucaracha|rata|basura)/iu',
            '/\b(hasta (un |una ))(niño|bebe|bebé|mono|perro)\s*(sabe|entiende|es) mas|más/iu',
            
            // Invalidación
            '/\b(tu opinion|tu opinión|lo que dices|lo que piensas)\s*(no (vale|importa|cuenta|sirve)|es basura)/iu',
            '/\b(nadie te|quien te|quién te)\s*(quiere|valora|respeta|escucha)/iu',
            '/\b(mejor (no|nunca))\s*(hables|escribas|comentes|participes)/iu',
            '/\b(callate|cállate)\s*(la boca|el hocico|mejor)/iu',
            
            // Inglés
            '/\b(you are (a )?(waste|worthless|useless|pathetic|loser|failure))/iu',
            '/\b(you (suck|are trash|are garbage))/iu',
            '/\b(nobody (likes|cares|wants) you)/iu',
            '/\b(you should (die|disappear))/iu',
        ];
        
        foreach ($patronesAcoso as $patron) {
            if (preg_match($patron, $textoNorm)) {
                error_log("❌ ACOSO/HUMILLACIÓN detectado");
                return $this->crearResultado(
                    false,
                    "Contenido de acoso o humillación detectado - Por favor mantén un ambiente respetuoso",
                    0.92,
                    'Filtro_Acoso',
                    [
                        'palabra_detectada' => 'acoso psicológico',
                        'categoria' => 'harassment'
                    ]
                );
            }
        }
        
        // Detectar múltiples términos negativos
        $palabrasNegativas = ['nada', 'fracaso', 'perdedor', 'inutil', 'basura', 'desperdicio', 'patético'];
        $contadorNegativos = 0;
        
        foreach ($palabrasNegativas as $palabra) {
            if (strpos($textoNorm, $this->normalizarTexto($palabra)) !== false) {
                $contadorNegativos++;
            }
        }
        
        if ($contadorNegativos >= 2 && strlen($texto) < 100) {
            error_log("❌ ACOSO: múltiples términos despectivos");
            return $this->crearResultado(
                false,
                "Lenguaje despectivo múltiple detectado - Por favor mantén un tono constructivo",
                0.85,
                'Filtro_Acoso',
                ['palabra_detectada' => 'múltiples términos despectivos']
            );
        }
        
        return null;
    }
    
    // ANÁLISIS CON IA
    
    private function analizarConOpenAI($texto) {
        error_log("🤖 Usando OpenAI Moderation API");
        
        $url = "https://api.openai.com/v1/moderations";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['input' => $texto]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Error OpenAI HTTP {$httpCode}: {$response}");
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['results'])) {
            throw new Exception("Respuesta inválida de OpenAI");
        }
        
        return $this->procesarResultadoOpenAI($data, $texto);
    }
    
    private function analizarConHuggingFace($texto) {
        error_log("🤖 Usando Hugging Face");
        
        $modelo = "unitary/toxic-bert";
        $url = "https://api-inference.huggingface.co/models/{$modelo}";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'inputs' => $texto,
                'options' => ['wait_for_model' => true]
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Error HuggingFace HTTP {$httpCode}");
        }
        
        $resultado = json_decode($response, true);
        return $this->procesarResultadoHuggingFace($resultado, $texto);
    }
    
    private function analizarConPatrones($texto) {
        error_log("⚙️ Usando análisis por patrones");
        
        if ($this->contieneSpam($texto)) {
            return $this->crearResultado(false, 'Contenido de spam detectado', 0.9, 'Patrones');
        }
        
        return $this->crearResultado(true, '', 0, 'Patrones');
    }
    
    // PROCESADORES DE RESULTADOS
    
    private function procesarResultadoOpenAI($data, $textoOriginal) {
        $result = $data['results'][0] ?? [];
        $flagged = $result['flagged'] ?? false;
        
        if (!$flagged) {
            error_log("✅ OpenAI: Contenido apropiado");
            return $this->crearResultado(true, '', 0, 'OpenAI_IA');
        }
        
        $categories = $result['categories'] ?? [];
        $scores = $result['category_scores'] ?? [];
        
        $maxScore = 0;
        $categoriaMax = null;
        
        foreach ($categories as $cat => $activa) {
            if ($activa) {
                $score = $scores[$cat] ?? 0;
                if ($score > $maxScore) {
                    $maxScore = $score;
                    $categoriaMax = $cat;
                }
            }
        }
        
        error_log("❌ OpenAI: Inapropiado - {$categoriaMax}");
        
        return $this->crearResultado(
            false,
            $this->obtenerRazonHumana($categoriaMax),
            $maxScore,
            'OpenAI_IA',
            ['categoria' => $categoriaMax]
        );
    }
    
    private function procesarResultadoHuggingFace($resultado, $textoOriginal) {
        $clasificaciones = is_array($resultado[0]) ? $resultado[0] : $resultado;
        
        $maxScore = 0;
        $categoriaDetectada = null;
        
        foreach ($clasificaciones as $item) {
            $score = floatval($item['score'] ?? 0);
            if ($score > $maxScore) {
                $maxScore = $score;
                $categoriaDetectada = $item['label'] ?? '';
            }
        }
        
        $esInapropiado = $maxScore >= 0.5;
        
        return $this->crearResultado(
            !$esInapropiado,
            $esInapropiado ? $this->obtenerRazonHumana($categoriaDetectada) : '',
            $maxScore,
            'HuggingFace_IA',
            ['categoria' => $categoriaDetectada]
        );
    }
    
    // MÉTODOS AUXILIARES    
    private function contieneSpam($texto) {
        $patronesSpam = [
            '/\b(viagra|cialis|casino|poker|lottery|prize)\b/i',
            '/https?:\/\/[^\s]+\.(xyz|top|loan|zip|click)/i',
            '/\b[A-Z]{15,}\b/',
            '/(.)\1{20,}/',
        ];
        
        foreach ($patronesSpam as $patron) {
            if (preg_match($patron, $texto)) {
                return true;
            }
        }
        
        return preg_match_all('/https?:\/\//', $texto) > 5;
    }
    
    private function normalizarTexto($texto) {
        $texto = mb_strtolower($texto, 'UTF-8');
        $acentos = [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 
            'ü'=>'u', 'ñ'=>'n'
        ];
        $texto = strtr($texto, $acentos);
        $texto = preg_replace('/[*_@#$%&]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }
    
    private function obtenerRazonHumana($categoria) {
        $razones = [
            'hate' => 'Discurso de odio detectado',
            'harassment' => 'Contenido de acoso detectado',
            'violence' => 'Contenido violento detectado',
            'sexual' => 'Contenido sexual inapropiado',
            'self-harm' => 'Contenido de autolesión detectado',
            'toxic' => 'Contenido tóxico detectado',
            'insult' => 'Insulto detectado',
            'threat' => 'Amenaza detectada',
        ];
        
        return $razones[$categoria] ?? 'Contenido inapropiado detectado';
    }
    
    private function crearResultado($apropiado, $razon, $confianza, $metodo, $extra = []) {
        return array_merge([
            'apropiado' => $apropiado,
            'razon' => $razon,
            'confianza' => $confianza,
            'metodo' => $metodo
        ], $extra);
    }
    
    private function finalizarAnalisis($resultado, $inicio, $hash) {
        $tiempo = round((microtime(true) - $inicio) * 1000, 2);
        $resultado['tiempo_ms'] = $tiempo;
        $this->stats['tiempo_total'] += $tiempo;
        
        if ($resultado['apropiado']) {
            $this->stats['aprobados']++;
        } else {
            $this->stats['rechazados']++;
        }
        
        if (isset($resultado['metodo']) && strpos($resultado['metodo'], 'IA') !== false) {
            $this->stats['por_ia']++;
        } else {
            $this->stats['por_patrones']++;
        }
        
        $this->cache[$hash] = $resultado;
        
        error_log("✓ Análisis completado en {$tiempo}ms");
        error_log("Resultado: " . ($resultado['apropiado'] ? '✅ APROPIADO' : '❌ RECHAZADO'));
        error_log("========================================");
        
        return $resultado;
    }
    
    public function obtenerEstadisticas() {
        return [
            'total_analisis' => $this->stats['total'],
            'aprobados' => $this->stats['aprobados'],
            'rechazados' => $this->stats['rechazados'],
            'porcentaje_aprobacion' => $this->stats['total'] > 0 
                ? round($this->stats['aprobados'] / $this->stats['total'] * 100, 1) 
                : 0,
            'por_ia' => $this->stats['por_ia'],
            'por_patrones' => $this->stats['por_patrones'],
            'tiempo_promedio_ms' => $this->stats['total'] > 0 
                ? round($this->stats['tiempo_total'] / $this->stats['total'], 2) 
                : 0,
            'cache_entradas' => count($this->cache)
        ];
    }
}
?>