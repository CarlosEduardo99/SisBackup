#!/bin/bash

# Verifica se o arquivo de configuração foi fornecido como argumento
if [ "$#" -ne 1 ]; then
    echo "Uso: $0 <arquivo_de_configuracao>"
    exit 1
fi

# Lê as configurações do arquivo de configuração
config_file="$1"
if [ -f "$config_file" ]; then
    source "$config_file"
else
    echo "Arquivo de configuração não encontrado: $config_file"
    exit 1
fi

# Diretório do Host
directory="$backup_directory/$backup_prefix" 
type_dir=''

# Cria diretório do host
if [ -d "$directory" ]; then
    cd "$directory"
else
    mkdir "$directory"

    # Verifique se a criação foi bem-sucedida
    if [ $? -eq 0 ]; then
        cd "$directory"
    else
        echo "Erro ao criar o diretório. Saindo do script."
        exit 1
    fi
fi

# Cria diretório de logs
if [ -d "$directory/log" ]; then
    :
else
    mkdir "$directory/log"

    # Verifique se a criação foi bem-sucedida
    if [ $? -eq 0 ]; then
        :
    else
        echo "Erro ao criar o diretório. Saindo do script."
        exit 1
    fi
fi

# Crie o arquivo de log
log_file="$directory/log/backup_$(date +'%Y%m%d').log"
touch "$log_file"

# Função para registrar logs
log() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >> "$log_file"
}

# Verifica o tipo de backup
if [ $backup_type == 'inc' ]; then
    # Nome do diretório que receberá os arquivos
    # Diretório para comparação
    type_dir="incremental"
    
    # Cria diretório do host
    if [ -d "$type_dir" ]; then
        cd "$type_dir"
    else
        log "Criando diretório incremental"
        mkdir "$type_dir"

        # Verifique se a criação foi bem-sucedida
        if [ $? -eq 0 ]; then
            cd "$type_dir"
        else
            echo "Erro ao criar o diretório. Saindo do script."
            exit 1
        fi
    fi
fi

# Realiza o backup usando rsync para cada diretório
for source_directory in "${source_directories[@]}"; do
    
    log "Iniciando backup de $source_directory"
    rsync -avzcr --delete "$remote_server:$source_directory" ./ >> "$log_file" 2>&1

    # Verifica erros
    if [ $? -ne 0 ]; then
        log "Erro durante o backup de $source_directory. Verifique o log para mais detalhes."
    else
        log "Backup de $source_directory concluído com sucesso."
    fi
done

# Realiza o dump dos bancos de dados MySQL (se backup_databases for verdadeiro)
if [ "$backup_databases" = true ]; then
    # Cria diretório do Banco de dados
    if [ -d "$directory/database" ]; then
        cd "$directory/database"
    else
        mkdir "$directory/database"

        # Verifique se a criação foi bem-sucedida
        if [ $? -eq 0 ]; then
            cd "$directory/database"
        else
            echo "Erro ao criar o diretório. Saindo do script."
            exit 1
        fi
    fi

    for database_name in "${database_names[@]}"; do
        log "Iniciando dump do banco de dados: $database_name"
        mysqldump --user="$mysql_user" --password="$mysql_password" --host="$mysql_host" $database_name > "$directory/database/$database_name.sql"

        # Verifica erros
        if [ $? -ne 0 ]; then
            log "Erro durante o dump do banco de dados: $database_name. Verifique o log para mais detalhes."
        else
            log "Dump do banco de dados $database_name concluído com sucesso."
        
        fi

    done

    # Compacta os bancos de dados usando tar
    log "Compactando arquivos database"
    tar -czf "$directory/${backup_prefix}_databases_$(date +'%Y%m%d').tar.gz" ./ >> "$log_file" 2>&1

else
    log "Nenhum banco de dados para fazer backup."
fi

# Compacta os backups usando tar
log "Compactando backups dos arquivos"
cd $directory/$type_dir
tar -czf "$directory/${backup_prefix}_files_$(date +'%Y%m%d').tar.gz" ./ >> "$log_file" 2>&1



# Remove backups mais antigos (mantém a quantidade especificada)
cd "$directory"
log "Removendo backups antigos"
backup_files=($(ls -t ${backup_prefix}_files*.tar.gz | sort -r))
count=0

for file in "${backup_files[@]}"; do
    if [ $count -ge $backup_count ]; then
        log "Removendo: $file"
        rm "$file" >> "$log_file" 2>&1
    fi
    count=$((count + 1))
done

# Remove backups mais antigos (mantém a quantidade especificada)
cd "$directory"
log "Removendo databases antigos"
backup_files=($(ls -t ${backup_prefix}_databases*.tar.gz | sort -r))
count=0

for file in "${backup_files[@]}"; do
    if [ $count -ge $backup_count ]; then
        log "Removendo: $file"
        rm "$file" >> "$log_file" 2>&1
    fi
    count=$((count + 1))
done

log "Backup concluído"
