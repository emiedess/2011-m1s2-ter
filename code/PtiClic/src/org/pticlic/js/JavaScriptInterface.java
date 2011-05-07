package org.pticlic.js;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

public class JavaScriptInterface {
	private Activity mContext;
	private ProgressDialog dialog;
	private String screen;

    /** Instantie l'interface et initialise le context */
    public JavaScriptInterface(Activity c) {
        mContext = c;
    }
    
    /**
     * Permet de setter une valeur dans les preferences
     * 
     * @param aName Le nom de la preference 
     * @param aValue La valeur que l'on veux pour la preference
     */
    public void setPreference(String aName, String aValue) {
    	SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(mContext);
    	prefs.edit().putString(aName, aValue).commit();
    }
    
    /** Permet de recupere une des preferences du systeme.
     * 
     * @param pref La preference que l'on veux recupere
     * @return La preference a recupere.
     */
    public String getPreference(String aName) {
    	SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(mContext);
    	return prefs.getString(aName, "");
    }
    
    /** Permet d'afficher une progressbar 
     *	@param title Le titre a afficher par la ProgressBar
     *	@param message Le message a afficher par la progressBar 
     */
    public void show(String title, String message) {
    	dialog = ProgressDialog.show(mContext, title, message);
    }
    
    public void info(String title, String message) {
    	AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
    	builder.setMessage(message)
    	       .setCancelable(false)
    	       .setPositiveButton("OK", new DialogInterface.OnClickListener() {
    	           public void onClick(DialogInterface dialog, int id) {
    	                dialog.dismiss();
    	           }
    	       });
    	AlertDialog alert = builder.create();
    	alert.show();
    }
    
    /** Permet de retirer l'affichage de la boite de dialog
     * 
     */
    public void dismiss() {
        if (dialog.isShowing())
        	dialog.dismiss();
    }
    
    /** Permet de quitter l'application
     * 
     */
    public void exit() {
    	mContext.finish();
    }

	public void setScreen(String screen) {
		this.screen = screen;
	}

	public String getScreen() {
		return screen;
	}
}
