import { useState } from "react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { X, Cookie, Settings, Shield, BarChart3 } from "lucide-react";
import { useCookie } from "@/hooks/useCookie";

export const CookieConsent = () => {
  const {
    showCookieBanner,
    acceptCookies,
    declineCookies,
    updateCookiePreferences,
    cookiePreferences,
  } = useCookie();
  const [showDetails, setShowDetails] = useState(false);

  if (!showCookieBanner) return null;

  return (
    <div className="fixed bottom-4 right-4 z-50 w-full max-w-xs sm:max-w-sm animate-in slide-in-from-bottom-2 duration-300">
      <Card className="bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border shadow-lg max-h-[80vh] p-3 md:p-4 overflow-y-auto">
        <CardHeader className="px-0">
          <div className="flex items-center gap-2">
            <Cookie className="h-5 w-5 text-amber-600" />
            <CardTitle className="text-base md:text-lg text-foreground font-semibold">
              🍪 {"Amazing Cookies!"}
            </CardTitle>
          </div>
          <CardDescription className="text-muted-foreground text-xs md:text-sm">
            {
              "We use cookies to enhance your experience and provide amazing features!"
            }
          </CardDescription>
        </CardHeader>

        <CardContent className="px-0">
          {!showDetails ? (
            <div className="space-y-3">
              <p className="text-sm text-muted-foreground">
                We use cookies to personalize content, analyze traffic, and
                improve your shopping experience.
              </p>
              <div className="flex items-center justify-between gap-2">
                <Button
                  onClick={acceptCookies}
                  className="flex-1 bg-primary hover:bg-primary/90"
                  size="sm">
                  {"Accept All"}
                </Button>
                <Button
                  onClick={declineCookies}
                  variant="outline"
                  className="flex-1"
                  size="sm">
                  {"Decline"}
                </Button>
              </div>
              <Button
                onClick={() => setShowDetails(true)}
                variant="secondary"
                size="sm"
                className="w-full text-xs">
                <Settings className="h-3 w-3 mr-1" />
                {"Customize Settings"}
              </Button>
            </div>
          ) : (
            <div>
              <div className="flex items-center justify-between">
                <h4 className="font-medium">Cookie Preferences</h4>
                <Button
                  onClick={() => setShowDetails(false)}
                  variant="ghost"
                  size="sm"
                  className="h-4 md:h-6 w-4 md:w-6 p-0">
                  <X className="h-3 w-3" />
                </Button>
              </div>
              <div className="space-y-3">
                <div className="flex items-center justify-between p-2 bg-muted/50 rounded-md">
                  <div className="flex items-center gap-2">
                    <Shield className="h-4 w-4 text-green-600" />
                    <div>
                      <p className="text-sm font-medium">Necessary</p>
                      <p className="text-xs text-muted-foreground">
                        Always active
                      </p>
                    </div>
                  </div>
                  <div className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                    Required
                  </div>
                </div>

                <div className="flex items-center justify-between p-2 bg-muted/50 rounded-md">
                  <div className="flex items-center gap-2">
                    <BarChart3 className="h-4 w-4 text-blue-600" />
                    <div>
                      <p className="text-sm font-medium">Analytics</p>
                      <p className="text-xs text-muted-foreground">
                        Help us improve
                      </p>
                    </div>
                  </div>
                  <Button
                    onClick={() =>
                      updateCookiePreferences({
                        analytics: !cookiePreferences.analytics,
                      })
                    }
                    variant={
                      cookiePreferences.analytics ? "default" : "outline"
                    }
                    size="sm"
                    className="h-6 text-xs">
                    {cookiePreferences.analytics ? "On" : "Off"}
                  </Button>
                </div>

                <div className="flex items-center justify-between p-2 bg-muted/50 rounded-md">
                  <div className="flex items-center gap-2">
                    <Cookie className="h-4 w-4 text-purple-600" />
                    <div>
                      <p className="text-sm font-medium">Marketing</p>
                      <p className="text-xs text-muted-foreground">
                        Personalized ads
                      </p>
                    </div>
                  </div>
                  <Button
                    onClick={() =>
                      updateCookiePreferences({
                        marketing: !cookiePreferences.marketing,
                      })
                    }
                    variant={
                      cookiePreferences.marketing ? "default" : "outline"
                    }
                    size="sm"
                    className="h-6 text-xs">
                    {cookiePreferences.marketing ? "On" : "Off"}
                  </Button>
                </div>
              </div>
              <div className="flex items-center justify-between gap-2 pt-2">
                <Button onClick={acceptCookies} className="flex-1" size="sm">
                  {"Save Preferences"}
                </Button>
                <Button
                  onClick={declineCookies}
                  variant="outline"
                  className="flex-1"
                  size="sm">
                  {"Decline All"}
                </Button>
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};
