package cloud.ambar.common.util;

import lombok.RequiredArgsConstructor;
import org.springframework.context.annotation.Bean;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class MongoInitializer {
    private final MongoInitializerApi mongoInitializerApi;

    @Bean
    public void initMongo() {
        mongoInitializerApi.initialize();
    }
}