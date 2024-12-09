package cloud.ambar.common.util;

import lombok.RequiredArgsConstructor;
import org.springframework.context.annotation.Bean;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class PostgresInitializer {
    private final PostgresInitializerApi postgresInitializerApi;

    @Bean
    public void initPostgres() {
        postgresInitializerApi.initialize();
    }
}